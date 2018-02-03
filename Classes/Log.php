<?php

namespace Pav\Daemon;

class Log implements LogInterface
{
    use LogRequiredPropertiesTrait;

    public static function toString()
    {
        self::$instance = !is_null(self::$instance) ? self::$instance : new Log();
        return implode(PHP_EOL, self::$instance->log);
    }

    public static function w($text)
    {
        self::$instance = !is_null(self::$instance) ? self::$instance : new Log();
        self::$instance->write($text);
    }

    public function write($text)
    {
        $this->log[] = $text;
        if (self::$debug)
            echo $text . PHP_EOL;
    }
}