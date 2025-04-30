<?php

namespace App\Service;

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
}