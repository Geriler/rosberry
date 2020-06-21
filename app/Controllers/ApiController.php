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

    public function login(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $errors = [];
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($password)) $errors[] = 'Password is required';
        if (count($errors)) {
            http_response_code(400);
            return ['errors' => $errors];
        }
        $user = $this->userModel->get('email', $email)[0];
        if (empty($user)) {
            http_response_code(400);
            return ['errors' => ['This user not found']];
        }
        if (!password_verify($password, $user->password)) {
            http_response_code(400);
            return ['errors' => ['Incorrect password']];
        }
        $token = $this->generateToken("$email:$password");
        $this->userModel->update($user->id, [
            'token' => $token,
            'lat' => $request->get('lat') ?? '',
            'lon' => $request->get('lon') ?? '',
        ]);
        http_response_code(200);
        return ['token' => $token];
    }

    public function register(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $errors = [];
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($password)) $errors[] = 'Password is required';
        if (empty($errors) && !empty($email) && $this->userModel->get('email', $email)) $errors[] = 'Email must be unique';
        if (count($errors)) {
            http_response_code(400);
            return ['errors' => $errors];
        }
        $password = password_hash($password, PASSWORD_BCRYPT);
        $result = $this->userModel->insert([
            'email' => $email,
            'password' => $password,
            'lat' => $request->get('lat') ?? '',
            'lon' => $request->get('lon') ?? '',
        ]);
        if ($result) {
            http_response_code(500);
            return ['errors' => ['Some error occurred']];
        } else {
            $token = $this->generateToken("$email:$password");
            $user = $this->userModel->get('email', $email)[0];
            $this->userModel->update($user->id, [
                'token' => $token,
                'lat' => $request->get('lat') ?? '',
                'lon' => $request->get('lon') ?? '',
            ]);
        }
        http_response_code(201);
        return ['token' => $token];
    }
}
