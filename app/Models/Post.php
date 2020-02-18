<?php

namespace App\Models;

use App\Core\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $columns = [
        'id',
        'title',
        'intro',
        'content',
        'is_published',
        'created_at',
        'updated_at',
    ];
}
