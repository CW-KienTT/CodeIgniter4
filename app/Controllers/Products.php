<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function index(): string
    {
        return '<h1>products list</h1>';
    }

    public function view(int $n): string
    {
        return '<h1>products detail ' . $n .'</h1>';
    }
}
