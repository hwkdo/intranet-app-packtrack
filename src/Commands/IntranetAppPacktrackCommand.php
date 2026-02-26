<?php

namespace Hwkdo\IntranetAppPacktrack\Commands;

use Illuminate\Console\Command;

class IntranetAppPacktrackCommand extends Command
{
    public $signature = 'intranet-app-packtrack';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
