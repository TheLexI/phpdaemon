<?php
/**
 * Created by PhpStorm.
 * User: lexi
 * Date: 04.02.18
 * Time: 3:38
 */

namespace Pav\Daemon;

/**
 * Class WorkerConfig
 * @package Pav\Daemon
 * @property boolean $daemon
 * @property integer $maxProcesses
 * @property integer $sleepTime
 * @property callable $worker
 */
class WorkerConfig
{
    /*По умолчанию в режиме обработчика очереди*/
    public $daemon;
    /*Максимальное количество воркеров (количество потоков в которые отрабатывается очередь)*/
    public $maxProcesses;
    /*Время ожидания перед попыткой запуска очередного потока, если все воркеры заняты*/
    public $sleepTime;
    /* Функция воркера - тело демона, несущее полезную нагрузку на вход принимает параметр переданный в run
    * function ($pullRow) {}
    */
    public $worker;

    public function __construct(callable $worker, $daemon = false, $maxProcesses = 1, $sleepTime = 10)
    {
        $this->worker = $worker;
        $this->sleepTime = min([max([1, $sleepTime]), 3600]);
        $this->maxProcesses = min([max([1, $maxProcesses]), 50]);
        $this->daemon = (boolean)$daemon;
    }
}