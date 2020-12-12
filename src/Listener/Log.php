<?php

namespace Eris\Listener;

use Eris\Listener;
use Eris\Listener\EmptyListener;
use Exception;

function log(string $file): Log
{
    return new Log($file, 'time', getmypid());
}

class Log extends EmptyListener implements Listener
{
    private string $file;
    /**
     * @var resource $fp
     */
    private $fp;
    /**
     * @var callable $time
     */
    private $time;
    private int $pid;

    /**
     * @param callable $time
     */
    public function __construct(string $file, $time, int $pid)
    {
        $this->file = $file;
        if (($this->fp = fopen($file, 'w')) === false) {
            throw new Exception("File could not be opened", 1);
        }

        $this->time = $time;
        $this->pid = $pid;
    }

    public function newGeneration(array $generation, $iteration)
    {
        $this->log(sprintf(
            "iteration %d: %s",
            $iteration,
            // TODO: duplication with collect
            json_encode(
                $generation
            )
        ));
    }

    /**
     * @psalm-suppress InvalidPropertyAssignmentValue
     */
    public function endPropertyVerification($ordinaryEvaluations, $iterations, Exception $exception = null)
    {
        fclose($this->fp);
    }

    public function failure(array $generation, Exception $exception)
    {
        $this->log(sprintf(
            "failure: %s. %s",
            // TODO: duplication with collect
            json_encode($generation),
            $exception->getMessage()
        ));
    }

    public function shrinking(array $generation)
    {
        $this->log(sprintf(
            "shrinking: %s",
            // TODO: duplication with collect
            json_encode($generation)
        ));
    }

    private function log(string $text): void
    {
        fwrite(
            $this->fp,
            sprintf(
                "[%s][%s] %s" . PHP_EOL,
                date('c', (int) call_user_func($this->time)),
                $this->pid,
                $text
            )
        );
    }
}
