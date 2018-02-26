<?php
/**
 * Created by PhpStorm.
 * User: robinam
 * Date: 06.12.17
 * Time: 12:18
 */

namespace App\Service\FileReader\FileReaders;

use App\Service\FileReader\IFileReader;
use Symfony\Component\Yaml\Yaml;

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
            $keys = $this->convertFileTitleToArrayKeys($contain[0]);

            $associativeArray = [];
            for ($itemNumber = 1; $itemNumber < count($contain); $itemNumber++) {
                for ($parameterNumber = 0; $parameterNumber < count($keys); $parameterNumber++) {
                    if (key_exists($parameterNumber, $contain[$itemNumber])) {
                        if ($contain[$itemNumber][$parameterNumber] != '') {
                            $parameter = $contain[$itemNumber][$parameterNumber];
                            $associativeArray[$itemNumber - 1][$keys[$parameterNumber]] = $parameter;
                        } else {
                            $associativeArray[$itemNumber - 1][$keys[$parameterNumber]] = null;
                        }
                    } else {
                        $associativeArray[$itemNumber - 1][$keys[$parameterNumber]] = null;
                    }
                }

            }

            return $associativeArray;
        }

        return null;
    }

    protected function convertFileTitleToArrayKeys(array $fileTitles): array
    {
        $associateFile = __DIR__ . '/csvFile.AssociateFields.yaml';

        $yaml = Yaml::parseFile($associateFile);

        $keys = [];
        foreach ($fileTitles as $fileTitle) {
            $searchResult = array_search($fileTitle, $yaml);
            $keys[] = $searchResult ? $searchResult : $fileTitle;
        }

        return $keys;
    }
}
