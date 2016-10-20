<?php

namespace App\Util;

class Options {
    protected $options;

    public function __construct($argc, $argv)
    {
        for ($i = 1; $i < $argc; $i++) {
            list($name, $value) = explode('=', $argv[$i]);

            $this->options[ str_replace('--', '', $name) ] = $value;
        }
    }

    public function get($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function has($name)
    {
        return isset($this->options[$name]);
    }
}
