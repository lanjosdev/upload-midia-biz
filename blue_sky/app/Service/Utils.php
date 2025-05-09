<?php

namespace App\Service;

use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class Utils
{
    //formata data e hora 
    function formattedDate($model, $params)
    {
        $formatedDateWithdrawalDate = explode(" ", $model->$params);

        $formatedHoursWithdrawalDate = $formatedDateWithdrawalDate[1];
        $formatedDateWithdrawalDate = explode('-', $formatedDateWithdrawalDate[0]);
        return $formatedDateWithdrawalDate[2] . '/' . $formatedDateWithdrawalDate[1] . '/' . $formatedDateWithdrawalDate[0] . ' ' . $formatedHoursWithdrawalDate;
    }

    //deleta arquivos se existirem
    function deleteFilesIfExist(?string $originalPath, ?string $fileName, ?string $destinationPath1080, ?string $destinationPath320, ?string $fullpath): void
    {
        $pathsToDelete = [];

        if (!empty($originalPath) && file_exists($originalPath)) {
            $pathsToDelete[] = $originalPath;
        }

        if (!empty($fileName) && !empty($destinationPath1080)) {
            $path1080 = $destinationPath1080 . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($path1080)) {
                $pathsToDelete[] = $path1080;
            }
        }

        if (!empty($fileName) && !empty($destinationPath320)) {
            $path320 = $destinationPath320 . DIRECTORY_SEPARATOR . $fileName;
            if (file_exists($path320)) {
                $pathsToDelete[] = $path320;
            }
        }

        if (!empty($fullpath)) {
            unlink($fullpath);
        }

        foreach ($pathsToDelete as $path) {
            unlink($path);
        }
    }

    function zabbix()
    {

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

        if (empty($query)) {
            // Enviar para o servidor Zabbix
            $zabbix_server = 'monitoramento.bizsys.com.br';
            $zabbix_port = '10051';
            $zabbix_key_rj = 'riodejaneiro';
            $zabbix_key_pe = 'recife';
            $zabbix_key_ce = 'fortaleza';
            $hostname = 'ceu-azul-7419';

            $cmd = "zabbix_sender -z $zabbix_server -p $zabbix_port -s \"$hostname\" -k \"$zabbix_key_ce\" -o $ce";
            exec($cmd);
            
            $cmd = "zabbix_sender -z $zabbix_server -p $zabbix_port -s \"$hostname\" -k \"$zabbix_key_pe\" -o $pe";
            exec($cmd);
            
            $cmd = "zabbix_sender -z $zabbix_server -p $zabbix_port -s \"$hostname\" -k \"$zabbix_key_rj\" -o $rj";
            exec($cmd);
            
        } else {
            echo "Nenhum resultado encontrado.";
        }
    }
}