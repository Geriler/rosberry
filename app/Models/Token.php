<?php namespace App\Models;

use App\Core\BaseModel;

class Token extends BaseModel
{
    protected $table = 'tokens';
    protected $fields = [
        'user_id',
        'token'
    ];
}
