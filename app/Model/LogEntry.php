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

    public function revisionUrl()
    {
        return $this->fields['revisionUrl'];
    }

    public function toArray()
    {
        return [
            'author'        => $this->author(),
            'case'          => $this->fogbugz(),
            'date'          => $this->date(),
            'message'       => $this->message(),
            'revision'      => $this->revision(),
            'revisionUrl'   => $this->revisionUrl() . $this->revision(),
        ];
    }

    public function toString()
    {
        return sprintf('%s - %s', $this->message(), $this->author());
    }

    public function toHtml()
    {
        $string = sprintf('<b>Commit Time:</b> %s', $this->date());

        if ($this->revisionUrl())
        {
            $string .= sprintf(', <b>Revision:</b> <a href="%s%s">%s</a>', $this->revisionUrl(), $this->revision(), $this->revision());
        }
        else
        {
            $string .= sprintf(', <b>Revision:</b> %s', $this->revision());
        }

        $string .= sprintf(', %s - <b>%s</b>', $this->message(), $this->author());

        if ($this->fogbugz())
        {
            $string .= sprintf(' <a href="https://daisydev.fogbugz.com/f/cases/%s">View case</a>', $this->fogbugz());
        }

        return sprintf('<p>%s</p>', $string);
    }
}
