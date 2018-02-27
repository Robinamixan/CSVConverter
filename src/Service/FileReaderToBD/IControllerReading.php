<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 27.2.18
 * Time: 12.44
 */

namespace App\Service\FileReaderToBD;


interface IControllerReading
{
    public function readFileToBD(\SplFileObject $file): array;
}