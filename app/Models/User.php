<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';

    protected $columns = [
        'id',
        'name',
        'email',
        'password',
        'created_at',
        'updated_at',
    ];
}
