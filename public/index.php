<?php
error_reporting(3);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Route;
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv(true);
$env = file_exists(__DIR__ . '/../.env') ? '.env' : '.env.example';
$dotenv->load(__DIR__ . '/../' . $env);
require_once __DIR__ . '/../app/routes.php';
Route::start();
