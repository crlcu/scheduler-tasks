<?php
namespace App\Console\Traits;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Exception;

trait Check
{
    private static $input;
    private static $output;
    private static $value;

    public static function check(InputInterface $input, OutputInterface $output, $value)
    {
        self::$input = $input;
        self::$output = $output;
        self::$value = $value;

        $method = $input->getOption('method');

        return self::$method();
    }

    private static function eq()
    {
        return self::$value == self::$input->getOption('value');
    }

    private static function ne()
    {
        return self::$value != self::$input->getOption('value');
    }

    private static function lt()
    {
        return self::$value < self::$input->getOption('value');
    }

    private static function gt()
    {
        return self::$value > self::$input->getOption('value');
    }

    private static function margin()
    {
        $min = self::$input->getOption('min');
        $max = self::$input->getOption('max');

        return self::$value < $min or self::$value > $max;
    }

    private static function match()
    {
        return preg_match(self::$input->getOption('regex'), self::$value);
    }
}
