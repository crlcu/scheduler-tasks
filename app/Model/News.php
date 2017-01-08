<?php
namespace App\Model;

use Carbon\Carbon;

class News {
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function fullContent()
    {
        return sprintf('%s %s', $this->fields['title'], $this->fields['description']);
    }

    public function get($field)
    {
        return $this->fields[$field];
    }

    public function condition()
    {
        $ok = false;

        $words = [
            'accident',
            'maşina', 'masina',
            'rănit', 'ranit', 'raniti', 'răniţi',
            'rutier',
            'victimă', 'victime'
        ];
        
        $pattern = sprintf('/(auto)?.*(%s).*(auto|rutier)?/i', join('|', $words));

        if (preg_match($pattern, $this->fullContent(), $matches)) {
            $ok = !empty(array_intersect($matches, ['accident', 'auto', 'maşina', 'masina', 'rutier']));
        }

        return $ok;
    }

    private function howManyRegex()
    {  
        $numbers = [
            'unu',
            'doi',
            'trei',
            'patru',
            'cinci',
            'şase', 'sase',
            'şapte', 'sapte',
            'opt',
            'nouă', 'noua',
            'zece'
        ];

        $patterns = [];

        foreach ($numbers as $number) {
            $patterns[] = sprintf('%s [\w\s]+ (răniţi|raniti|mort|morţi|murit)', $number);
        }

        return sprintf('/%s/', join('|', $patterns));
    }

    public function howMany()
    {
        if (preg_match($this->howManyRegex(), $this->fullContent(), $matches)) {
            dump($matches);
            dump(current($matches));
            dump(end($matches));
        }
    }
}
