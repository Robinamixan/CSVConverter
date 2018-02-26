<?php
/**
 * Created by PhpStorm.
 * User: robinam
 * Date: 06.12.17
 * Time: 12:18
 */

namespace App\Service\FileReader\FileReaders;

use App\Service\FileReader\IFileReader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Yaml\Yaml;

class CSVReader implements IFileReader
{
    private $file;
    private $keys;

    public function __construct(\SplFileObject $file)
    {
        $this->file = $file;
        $titles = $this->file->fgetcsv(',');
        $this->keys = $this->convertFileTitleToArrayKeys($titles);
    }

    public function getNextItem(): ?array
    {
        $csvLineArray = $this->file->fgetcsv(',');
        if ((count($csvLineArray) !== 1) && (!is_null($csvLineArray[0]))) {

            return $this->convertArrayToAssociativeArray($csvLineArray);
        }

        return null;
    }

    protected function convertArrayToAssociativeArray(array $indexArray): array
    {
        if (!is_null($this->keys)) {
            $associativeArray = [];
            for ($parameterNumber = 0; $parameterNumber < count($this->keys); $parameterNumber++) {
                if (key_exists($parameterNumber, $indexArray)) {
                    if ($indexArray[$parameterNumber] != '') {
                        $parameterValue = $indexArray[$parameterNumber];
                        $associativeArray[$this->keys[$parameterNumber]] = $parameterValue;
                    } else {
                        $associativeArray[$this->keys[$parameterNumber]] = null;
                    }
                } else {
                    $associativeArray[$this->keys[$parameterNumber]] = null;
                }
            }

            return $associativeArray;
        }

        return null;
    }

    protected function convertFileTitleToArrayKeys(array $fileTitles): array
    {
        $associateFile = 'csvFile.AssociateFields.yaml';
        $locator = new FileLocator(__DIR__);
        $yaml = Yaml::parseFile($locator->locate($associateFile));

        $keys = [];
        foreach ($fileTitles as $fileTitle) {
            $searchResult = array_search($fileTitle, $yaml);
            $keys[] = $searchResult ? $searchResult : $fileTitle;
        }

        return $keys;
    }
}
