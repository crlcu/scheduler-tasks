<?php
namespace App\Model;

use Carbon\Carbon;

class News {
    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;

        // Set aditional fields
        $this->fields['date'] = new Carbon($this->fields['pubDate']);
    }

    public function fullContent()
    {
        return sprintf('%s %s', $this->title, $this->description);
    }

    public function isAboutCarCrashes()
    {
        $ok = false;

        $words = [
            'accident',
            '(auto|maşina|maşină|rutier|stradă|strada|tir)'
        ];

        $pattern = sprintf('/(%s)/i', join('[\d\D\w\W\s]+', $words));

        if (preg_match($pattern, $this->fullContent(), $matches)) {
            $ok = true;
        }

        return $ok;
    }

    public function isNewerThan($date)
    {
        $date = new Carbon($date);

        return $this->date >= $date;
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

    public function __get($field)
    {
        return $this->fields[$field];
    }

    public function __toString()
    {
        return sprintf('%s - %s', $this->date, $this->title);
    }

    public function __toHtml()
    {
        return sprintf('%s - %s <a href="%s">Vezi stirea</a>', $this->date, $this->title, $this->link);
    }
}
