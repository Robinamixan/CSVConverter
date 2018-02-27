<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 27.2.18
 * Time: 12.41
 */

namespace App\Service\FileReaderToBD;


class FileReaderToBD
{
    public function readFileToBD(\SplFileObject $file, IControllerReading $controllerReading): array
    {
        return $controllerReading->readFileToBD($file);
    }
}