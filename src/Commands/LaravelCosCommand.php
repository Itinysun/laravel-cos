<?php

namespace Itinysun\LaravelCos\Commands;

use Illuminate\Console\Command;

class LaravelCosCommand extends Command
{
    public $signature = 'laravel-cos';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
