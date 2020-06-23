<?php namespace App\Models;

use App\Core\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fields = [
        'email',
        'password',
        'name',
        'avatar',
        'age',
        'interests',
        'lat',
        'lon',
        'country',
    ];
}
