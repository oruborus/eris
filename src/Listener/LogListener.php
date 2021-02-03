<?php

declare(strict_types=1);

namespace Eris\Listener;

use Eris\Contracts\Listener;
use Eris\Listener\EmptyListener;
use Exception;
use InvalidArgumentException;
use Throwable;

use function date;
use function fopen;
use function fclose;
use function fwrite;
use function json_encode;

use function sprintf;
use const PHP_EOL;

class LogListener extends EmptyListener implements Listener
{
    /**
     * @var resource $fp
     */
    private $fp;

    /**
     * @var callable():int $time
     */
    private $time;

    private int $pid;

    /**
     * @param callable():int $time
     */
    public function __construct(string $file, $time, int $pid)
    {
        try {
            $handle = fopen($file, 'w');
        } catch (Throwable $th) {
            $handle = false;
        }

        if (!$handle) {
            throw new InvalidArgumentException("File could not be opened", 1);
        }

        $this->fp   = $handle;
        $this->time = $time;
        $this->pid  = $pid;
    }

    public function __destruct()
    {
        fclose($this->fp);
        unset($this->fp);
    }

    public function newGeneration(array $generation, int $iteration): void
    {
        $this->log(sprintf(
            'iteration %d: %s',
            $iteration,
            // TODO: duplication with collect
            json_encode(
                $generation
            )
        ));
    }

    public function failure(array $generation, Exception $exception): void
    {
        $this->log(sprintf(
            'failure: %s. %s',
            // TODO: duplication with collect
            json_encode($generation),
            $exception->getMessage()
        ));
    }

    public function shrinking(array $generation): void
    {
        $this->log(sprintf(
            'shrinking: %s',
            // TODO: duplication with collect
            json_encode($generation)
        ));
    }

    private function log(string $text): void
    {
        fwrite(
            $this->fp,
            sprintf(
                '[%s][%s] %s' . PHP_EOL,
                date('c', ($this->time)()),
                $this->pid,
                $text
            )
        );
    }
}
