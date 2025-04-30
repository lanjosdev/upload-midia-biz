<?php

namespace App\Console\Commands;

use App\Jobs\ProcessVideoJob;
use Illuminate\Console\Command;

class QueuesProcessVideoCommand extends Command
{
    protected $signature = 'video:process-video';
    protected $description = 'Processa o vídeo recebido';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        ProcessVideoJob::dispatch();

        $this->info('Processando vídeo(s) ...');
    }
}