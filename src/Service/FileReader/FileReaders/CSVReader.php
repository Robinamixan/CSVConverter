<?php
/**
 * Created by PhpStorm.
 * User: robinam
 * Date: 06.12.17
 * Time: 12:18
 */

namespace App\Service\FileReader\FileReaders;

use App\Service\FileReader\IFileReader;

class CSVReader implements IFileReader
{
    public function getFileContain(\SplFileObject $file): array
    {
        $fileContain = [];
        if ($handle = fopen($file->getPathname(), "r")) {
            while ($fileRow = fgetcsv($handle, 1000, ",")) {
                $fileContain[] = $fileRow;
            }
            fclose($handle);
        }

        return $fileContain;
    }
}
