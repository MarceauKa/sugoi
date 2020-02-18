<?php

namespace App\Models;

use App\Core\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $columns = [
        'id',
        'author',
        'content',
        'is_published',
        'reports_count',
        'created_at',
        'updated_at',
    ];
}
