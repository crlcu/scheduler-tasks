<?php
namespace App\Schemas;

class LaravelNews
{
    public function match(\App\Models\News $item)
    {
        return true;
    }
}
