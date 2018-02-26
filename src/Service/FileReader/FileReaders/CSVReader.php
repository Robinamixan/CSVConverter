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

        return $this->convertContainToAssociativeArray($fileContain);
    }

    protected function convertContainToAssociativeArray(array $contain): array
    {
        if (key_exists(0, $contain)) {
            $titles = $contain[0];
            $titles[] = "end of string";
            $associativeArray = [];
            for ($itemNumber = 1; $itemNumber < count($contain); $itemNumber++) {
                for ($parameterNumber = 0; $parameterNumber < count($titles) - 1; $parameterNumber++) {
                    if (key_exists($parameterNumber, $contain[$itemNumber])) {
                        if ($contain[$itemNumber][$parameterNumber] != '') {
                            $parameter = $contain[$itemNumber][$parameterNumber];
                            $associativeArray[$itemNumber - 1][$titles[$parameterNumber]] = $parameter;
                        } else {
                            $associativeArray[$itemNumber - 1][$titles[$parameterNumber]] = null;
                        }
                    } else {
                        $associativeArray[$itemNumber - 1][$titles[$parameterNumber]] = null;
                    }
                }
            }

            return $associativeArray;
        }

        return null;
    }
}
