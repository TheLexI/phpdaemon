<?php
/**
 * Created by PhpStorm.
 * User: lexi
 * Date: 04.02.18
 * Time: 2:15
 */

namespace Pav\Daemon;

interface LogInterface
{
    /**
     * @return string
     */
    public static function toString();

    /**
     * @param string $text
     */
    public static function w($text);

    /**
     * @param string $text
     */
    public function write($text);
}