<?php

namespace App\Controllers;

use App\Models\Post;
use App\Models\User;

class HomeController
{
    public function index()
    {
        $persons = collect([
            0 => [
                'id' => 1,
                'name' => 'Marceau',
                'admin' => true,
            ],
            1 => [
                'id' => 2,
                'name' => 'Thibault',
                'admin' => true,
            ],
            2 => [
                'id' => 3,
                'name' => 'Thomas',
                'admin' => false,
            ],
        ]);

        return response()->view('home', [
            'persons' => $persons
        ]);
    }

    public function show(string $name)
    {
        return response()->view('home', [
            'name' => $name
        ]);
    }

    public function redirect()
    {
        return response()->redirect(url()->route('home'));
    }
}
