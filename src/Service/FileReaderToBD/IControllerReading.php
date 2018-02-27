<?php

namespace App\Service\FileReaderToBD;


interface IControllerReading
{
    public function readFileToBD(\SplFileObject $file): array;
}
