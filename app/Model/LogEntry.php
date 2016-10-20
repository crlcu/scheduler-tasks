<?php
namespace App\Model;

use Carbon\Carbon;

class LogEntry {

    protected $fields;

    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    public function author()
    {
        return $this->fields['author'];
    }

    public function date()
    {
        return (new Carbon($this->fields['date']))
            ->toDateTimeString();
    }

    public function fogbugz()
    {
        $message = $this->message();
        $matches = [];

        $ok = preg_match('/(fb|fogbugz|case)(\d+)(:|\s-|\s)/i', $message, $matches);

        return isset($matches[2]) ? $matches[2] : null;
    }

    public function message()
    {
        return $this->fields['msg'];
    }

    public function revision()
    {
        return $this->fields['@attributes']['revision'];
    }

    public function toArray()
    {
        return [
            'author'    => $this->author(),
            'case'      => $this->fogbugz(),
            'date'      => $this->date(),
            'message'   => $this->message(),
            'revision'  => $this->revision(),
        ];
    }

    public function toString()
    {
        return sprintf('%s - %s', $this->message(), $this->author());
    }
}
