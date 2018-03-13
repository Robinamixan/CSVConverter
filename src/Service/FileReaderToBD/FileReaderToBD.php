<?php

namespace App\Service\FileReaderToBD;

class FileReaderToBD
{
    public function readFileToBD(\SplFileObject $file, IControllerReading $controllerReading): array
    {
        return $controllerReading->readFileToBD($file);
    }
}
