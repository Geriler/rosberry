<?php namespace App\Controllers;

class ApiController
{
    public function __construct()
    {
        header('Content-type: application/json');
    }

    public function login()
    {
        return 'login';
    }

    public function register()
    {
        return 'register';
    }
}
