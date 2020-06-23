<?php namespace App\Controllers;

use App\Core\Request;
use App\Models\Setting;
use App\Models\Token;
use App\Models\User;

class ApiController
{
    private $userModel;
    private $tokenModel;
    private $settingModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->tokenModel = new Token();
        $this->settingModel = new Setting();
        header('Content-type: application/json');
    }

    private function generateToken()
    {
        return bin2hex(random_bytes(64));
    }

    private function authentication($token)
    {
        if (empty($token)) {
            http_response_code(401);
            return ['errors' => ['Token is required']];
        }
        $token = $this->tokenModel->get('token', $token)[0];
        if (empty($token)) {
            http_response_code(400);
            return ['errors' => ['Invalid token']];
        }
        return $token;
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
        $token_id = $this->tokenModel->get('user_id', $user->id)[0]->id;
        $token = $this->generateToken();
        $this->tokenModel->update($token_id, [
            'token' => $token,
        ]);
        $this->userModel->update($user->id, [
            'lat' => $request->get('lat') ?? $user->lat ?? 0,
            'lon' => $request->get('lon') ?? $user->lon ?? 0,
            'country' => $request->get('country') ?? $user->country ?? '',
        ]);
        http_response_code(200);
        return ['token' => $token];
    }

    public function logout(Request $request)
    {
        $token = $this->authentication($request->get('token'));
        $this->tokenModel->update($token->id, [
            'token' => '',
        ]);
        $user = $this->userModel->get('id', $token->user_id)[0];
        $this->userModel->update($user->id, [
            'lat' => $request->get('lat') ?? $user->lat,
            'lon' => $request->get('lon') ?? $user->lon,
            'country' => $request->get('country') ?? $user->country,
        ]);
        return ['result' => 'ok'];
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
            'lat' => $request->get('lat') ?? 0,
            'lon' => $request->get('lon') ?? 0,
            'country' => $request->get('country') ?? '',
        ]);
        if ($result) {
            http_response_code(500);
            return ['errors' => ['Some error occurred']];
        } else {
            $token = $this->generateToken();
            $user = $this->userModel->get('email', $email)[0];
            $this->tokenModel->insert([
                'user_id' => $user->id,
                'token' => $token,
            ]);
            $this->settingModel->insert([
                'user_id' => $user->id,
                'show_age' => json_encode([0, 'max']),
                'show_self_age' => json_encode([0, 'max']),
                'show_interests' => json_encode([2, 2, 2]),
                'show_neighbors' => 0
            ]);
            $this->userModel->update($user->id, [
                'lat' => $request->get('lat') ?? $user->lat,
                'lon' => $request->get('lon') ?? $user->lon,
                'country' => $request->get('country') ?? $user->country,
            ]);
        }
        http_response_code(201);
        return ['token' => $token];
    }

    public function profileEdit(Request $request)
    {
        $token = $this->authentication($request->get('token'));
        $user = $this->userModel->get('id', $token->user_id)[0];
        if (!empty($age = $request->get('age')) && $age < 0) {
            http_response_code(400);
            return ['errors' => ['Invalid age']];
        }
        if (empty($request->get('password'))) {
            $password = $user->password;
        } else {
            $password = password_hash($request->get('password'), PASSWORD_BCRYPT);
        }
        $this->userModel->update($user->id, [
            'email' => $request->get('email') ?? $user->email,
            'password' => $password,
            'name' => $request->get('name') ?? $user->name,
            'age' => $age ?? $user->age,
            'avatar' => $request->get('avatar') ?? $user->avatar,
            'interests' => json_encode($request->get('interests')) ?? $user->interests,
            'lat' => $request->get('lat') ?? $user->lat,
            'lon' => $request->get('lon') ?? $user->lon,
            'country' => $request->get('country') ?? $user->country,
        ]);
        http_response_code(200);
        return ['result' => 'ok'];
    }

    public function profileGet(Request $request)
    {
        $token = $this->authentication($request->get('token'));
        $user = $this->userModel->get('id', $token->user_id)[0];
        $this->userModel->update($user->id, [
            'lat' => $request->get('lat') ?? $user->lat,
            'lon' => $request->get('lon') ?? $user->lon,
            'country' => $request->get('country') ?? $user->country,
        ]);
        return [
            'email' => $user->email,
            'name' => $user->name,
            'avatar' => $user->avatar,
            'age' => $user->age,
            'interests' => json_decode($user->interests),
        ];
    }

    public function settingsEdit(Request $request)
    {
        $token = $this->authentication($request->get('token'));
        $settings = $this->settingModel->get('user_id', $token->user_id)[0];
        $user = $this->userModel->get('id', $token->user_id)[0];
        $this->userModel->update($user->id, [
            'lat' => $request->get('lat') ?? $user->lat,
            'lon' => $request->get('lon') ?? $user->lon,
            'country' => $request->get('country') ?? $user->country,
        ]);
        $this->settingModel->update($settings->id, [
            'show_age' => !empty($show_age = $request->get('show_age')) ? json_encode($show_age) : $settings->show_age,
            'show_self_age' => !empty($show_self_age = $request->get('show_self_age')) ? json_encode($show_self_age) : $settings->show_self_age,
            'show_interests' => !empty($show_interests = $request->get('show_interests')) ? json_encode($show_interests) : $settings->show_interests,
            'show_neighbors' => $request->get('show_neighbors') ?? $settings->show_neighbors,
        ]);
        http_response_code(200);
        return ['result' => 'ok'];
    }
}
