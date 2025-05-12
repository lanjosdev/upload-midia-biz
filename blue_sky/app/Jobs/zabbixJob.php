<?php

namespace App\Jobs;

use App\Models\Media;
use App\Service\Utils;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class zabbixJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $query = Media::select('fk_region_id')
                ->selectRaw('COUNT(*) as media_original')
                ->groupBy('fk_region_id')
                ->get();

            $array = [];

            for ($i = 0; $i < count($query); $i++) {
                $region = $query[$i]->fk_region_id;
                $qtd_media = $query[$i]->media_original;

                $array[] = [$region => $qtd_media];
            }

            $query = $array;

            $ce = $query[0][1];
            $pe = $query[1][2];
            $rj = $query[2][3];

            if (!empty($query)) {
                
                $zabbix_server = 'monitoramento.bizsys.com.br';
                $zabbix_port = '10051';
                $zabbix_key_rj = 'riodejaneiro';
                $zabbix_key_pe = 'recife';
                $zabbix_key_ce = 'fortaleza';
                $hostname = 'ceu-azul-7419';

                $cmd1 = "zabbix_sender -z $zabbix_server -p $zabbix_port -s \"$hostname\" -k \"$zabbix_key_ce\" -o $ce";
                // echo ($cmd1);
                exec($cmd1);

                $cmd2 = "zabbix_sender -z $zabbix_server -p $zabbix_port -s \"$hostname\" -k \"$zabbix_key_pe\" -o $pe";
                // echo ($cmd2);
                exec($cmd2);

                $cmd3 = "zabbix_sender -z $zabbix_server -p $zabbix_port -s \"$hostname\" -k \"$zabbix_key_rj\" -o $rj";
                // echo ($cmd3);
                exec($cmd3);
            } else {
                echo "Nenhum resultado encontrado.";
            }
        } catch (\Throwable $e) {
            Log::error('Erro ao executar zabbixJob: ' . $e->getMessage());
        }
    }
}