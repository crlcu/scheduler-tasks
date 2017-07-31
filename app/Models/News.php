<?php
namespace App\Models;

use Carbon\Carbon;

class News {
    protected $fields;

    public function __construct($fields = [])
    {
        $this->fields = $fields;

        // Set aditional fields
        $this->fields['date'] = new Carbon(isset($this->fields['pubDate']) ? $this->fields['pubDate'] : 'now');
    }

    public function fullContent()
    {
        return sprintf('%s %s', $this->title, $this->description);
    }

    public function isNewerThan($date)
    {
        $date = new Carbon($date);

        return $this->date >= $date;
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
