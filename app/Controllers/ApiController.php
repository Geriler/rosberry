<?php namespace App\Controllers;

use App\Core\Request;
use App\Models\User;

class ApiController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
        header('Content-type: application/json');
    }

    private function generateToken(string $data)
    {
        return hash('sha512', $data);
    }

    public function login()
    {
        return 'login';
    }

    public function register(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $errors = [];
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($password)) $errors[] = 'Password is required';
        if (!empty($email) && $this->userModel->get('email', $email)) $errors[] = 'Email must be unique';
        if (count($errors)) return ['result' => 'error', 'errors' => $errors, 'status' => 400];
        $password = password_hash($password, PASSWORD_BCRYPT);
        $result = $this->userModel->insert([
            'email' => $email,
            'password' => $password,
        ]);
        if ($result) {
            return ['result' => 'error', 'errors' => ['Some error occurred'], 'status' => 500];
        } else {
            $token = $this->generateToken("$email:$password");
            $user = $this->userModel->get('email', $email)[0];
            $this->userModel->update($user->id, [
                'token' => $token,
            ]);
        }
        return ['result' => 'ok', 'token' => $token, 'status' => 200];
    }
}
