<?php
/**
 * Created by PhpStorm.
 * User: Пастушенко Алексей
 * Date: 13.12.17
 * Time: 18:55
 * p.s. не все приемы придумал сам, кое что подсмотрел у соседей.
 * ссылки к сожалению не помню, потому не обижайтесь
 */

namespace Pav\Daemon;

declare(ticks=1);
pcntl_signal_dispatch();

class DaemonClass
{
    /*По умолчанию в режиме обработчика очереди*/
    public $daemon = false;
    /*Максимальное количество воркеров (количество потоков в которые отрабатывается очередь)*/
    public $maxProcesses = 1;
    /*Время ожидания перед попыткой запуска очередного потока, если все воркеры заняты*/
    public $sleepTime = 10;
    /*Функция воркера - тело демона, несущее полезную нагрузку на вход принимает параметр переданный в run */
    public $worker;
    private $stop_server = false;
    /*Массив идентификаторов процессов воркеров.*/
    private $currentJobs = [];

    public function __construct(callable $worker)
    {
        Log::w(time() . ": Запуск контроллера");
        $this->worker = $worker;
        pcntl_signal(SIGTERM, array($this, "childSignalHandler"));
        pcntl_signal(SIGCHLD, array($this, "childSignalHandler"));
    }

    public function run($WorkerConfig)
    {
        Log::w(time() . ": Запуск воркера");
        $alreadySay = false;
        // Пока $stop_server не установится в TRUE, гоняем бесконечный цикл
        while (!$this->stop_server) {
            // Если уже запущено максимальное количество дочерних процессов, ждем их завершения
            while (count($this->currentJobs) >= $this->maxProcesses) {
                if (!$alreadySay) {
                    $alreadySay = true;
                    Log::w(time() . ": Запущено максимально процессов. Ожидаем! ..");
                }
                sleep($this->sleepTime);
            }
            if (!$this->stop_server) $this->launchJob($WorkerConfig);

            /*В режиме очереди, после окончания обработки данных, выходим из цикла*/
            if (!$this->daemon) break;
        }
    }

    protected function launchJob($WorkerConfig)
    {
        // Создаем дочерний процесс
        // весь код после pcntl_fork() будет выполняться
        // двумя процессами: родительским и дочерним
        $pid = pcntl_fork();
        if ($pid == -1) {
            // Не удалось создать дочерний процесс
            Log::w((time() . ': Не удалось создать дочерний процесс'));
            return FALSE;
        } elseif ($pid) {
            // Этот код выполнится родительским процессом
            $this->currentJobs[$pid] = TRUE;
        } else {
            // А этот код выполнится дочерним процессом
            Log::w(time() . ": Запускаю воркер с ID " . getmypid());
            ($this->worker)($WorkerConfig);
            exit();
        }
        return TRUE;
    }

    public function childSignalHandler($signo, $pid = null, $status = null)
    {
        switch ($signo) {
            case SIGTERM:
                // При получении сигнала завершения работы устанавливаем флаг
                $this->stop_server = true;
                Log::w(time() . ": Контроллер завершен");
                break;
            case SIGCHLD:
                // При получении сигнала от дочернего процесса
                if (!$pid) {
                    $pid = pcntl_waitpid(-1, $status, WNOHANG);
                }
                // Пока есть завершенные дочерние процессы
                while ($pid > 0) {
                    if ($pid && @isset($this->currentJobs[$pid])) {
                        Log::w($pid . "." . time() . ": Завершен воркер $pid");
                        // Удаляем дочерние процессы из списка
                        unset($this->currentJobs[$pid]);
                    }
                    $pid = pcntl_waitpid(-1, $status, WNOHANG);
                }
                break;
            default:
                print_r([$signo, $pid, $status]);
            // все остальные сигналы
        }
    }
}