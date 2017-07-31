<?php
namespace App\Schemas;

class CarCrash
{
    public function match(\App\Models\News $item)
    {
        $ok = false;

        $words = [
            'accident',
            '(auto|maşină|maşina|masina|rănit|ranit|răniţi|raniti|rutier|stradă|strada|tir)'
        ];

        $pattern = sprintf('/(%s)/i', join('[\d\D\w\W\s]+', $words));

        if (preg_match($pattern, $item->fullContent(), $matches))
        {
            $ok = true;
        }

        return $ok;
    }
}
