<?php

namespace App\Controllers;

class AdminController
{
    public function index()
    {
        return response()->view('admin');
    }
}
