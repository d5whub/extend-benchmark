<?php
/**
 * This file is part of d5whub extend benchmark
 * @author Vitor Reis <vitor@d5w.com.br>
 */

declare(strict_types=1);

namespace D5WHUB\Extend\Benchmark\Printer;

use D5WHUB\Extend\Benchmark\Utils\Printer;
use D5WHUB\Extend\Benchmark\Utils\Status;

class Console implements Printer
{
    private function withTime(string ...$values): void
    {
        foreach ($values as $value) {
            echo "\e[0;37m[" . date('Y-m-d H:i:s') . "] $value";
        }
    }

    public function start(): self
    {
        $this->withTime("\e[3;37mD5WHUB Extend Benchmark\n");
        $this->skipline();
        return $this;
    }

    public function title(string $title, ?string $comment): self
    {
        $title = trim($title);
        $this->withTime("\e[0;1;3m$title\n");
        if ($comment = trim($comment)) {
            $this->withTime("\e[0;1;3m$comment\n");
        }
        return $this;
    }

    public function skipline(int $times = 1): self
    {
        $this->withTime(...array_fill(1, $times, "\n"));
        return $this;
    }

    public function subtitle(string $title, ?string $comment = null, ?int $iterations = null): self
    {
        $title = trim($title);
        $comment = trim($comment);
        $iterations = $iterations ? " $iterations time(s)" : "";
        $comment = $comment ? " " . ($iterations ? "- " : "") . "$comment" : "";

        $this->withTime("\e[0;1m• \e[4;34m$title\e[0;1;37m$iterations$comment\n");
        return $this;
    }

    public function tmp(string $text): self
    {
        $text = trim($text);
        $this->withTime("\e[37m$text");
        return $this;
    }

    public function tmpclear(): self
    {
        echo "\r";
        return $this;
    }

    public function results(array $results, bool $end = false): self
    {
        $pad = max(array_map('strlen', array_map('trim', array_keys($results))));
        $best = current($results)['_']['average'];

        foreach ($results as $title => $result) {
            $title = str_pad($title, $pad);

            $text = '';

            switch ($result['_']['status']) {
                case Status::SUCCESS:
                    if ($best === $result['_']['average']) {
                        $text = sprintf(
                            "\e[0m| %s | %.11fs | baseline",
                            $title,
                            $result['_']['average']
                        );
                    } else {
                        $slower = round(($result['_']['average'] / $best) * 100, 2);
                        $text = sprintf(
                            "\e[0m| %s | %.11fs | %s (+%.11fs)",
                            $title,
                            $result['_']['average'],
                            "$slower% slower",
                            $result['_']['average'] - $best
                        );
                    }
                    break;

                case Status::PARTIAL:
                    $text = $end
                        ? sprintf("\e[0m| %s | \e[3;37mNot conclusive", $title)
                        : sprintf(
                            "\e[0m| %s | %.11fs | \e[3;33mPartial success, failed: %s",
                            $title,
                            $result['_']['average'],
                            current($result['_']['error'])
                        );
                    break;

                case Status::FAILED:
                    $text = $end
                        ? sprintf("\e[0m| %s | \e[3;37mNot conclusive", $title)
                        : sprintf("\e[0m| %s | \e[3;31mFailed: %s", $title, current($result['_']['error']));
                    break;
            }
            $this->withTime("$text\n");
        }
        return $this;
    }

    public function end(float $runningTime, int $totalBenchmark, int $totalInteractions): self
    {
        $this->withTime(sprintf(
            "\e[3;37mEnd %.11fs, %d benchmark(s) and %d interaction(s)\n",
            $runningTime,
            $totalBenchmark,
            $totalInteractions
        ));
        return $this;
    }
}