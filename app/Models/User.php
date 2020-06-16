<?php namespace App\Models;

use App\Core\BaseModel;

class User extends BaseModel
{
    protected $table = 'users';
    protected $fields = [
        'name',
        'avatar',
        'age',
        'interests',
    ];
}
