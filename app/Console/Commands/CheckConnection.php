<?php

namespace App\Console\Commands;

use App\Support\ConnectionCheck;
use Illuminate\Console\Command;

class CheckConnection extends Command
{
    protected $signature = 'culqi:check';

    protected $description = 'Verifica la conexión a la base de datos y a Culqi';

    public function handle(): int
    {
        return ConnectionCheck::render($this->output->getOutput())
            ? self::SUCCESS
            : self::FAILURE;
    }
}
