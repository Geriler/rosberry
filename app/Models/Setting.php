<?php namespace App\Models;

use App\Core\BaseModel;

class Setting extends BaseModel
{
    protected $table = 'settings';
    protected $fields = [
        'user_id',
        'show_age',
        'show_self_age',
        'show_interests',
        'show_neighbors'
    ];
}
