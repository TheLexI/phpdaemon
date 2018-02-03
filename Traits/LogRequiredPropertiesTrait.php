<?php
/**
 * Created by PhpStorm.
 * User: lexi
 * Date: 04.02.18
 * Time: 2:36
 */

namespace Pav\Daemon;

trait LogRequiredPropertiesTrait
{
    /**
     * @var bool
     */
    public static $debug;
    /**
     * @var \Pav\Daemon\Log
     */
    private static $instance;
    /**
     * @var array of string
     */
    private $log = [];
}