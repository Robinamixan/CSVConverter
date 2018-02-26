<?php

namespace App\Service\FileReader;

use App\Service\FileReader\FileReaders\CSVReader;

class FileReader
{
    private $fileReader;

    public function __construct()
    {
        $this->fileReader = null;
    }

    public function setFileForRead(\SplFileObject $file): bool
    {
        $this->fileReader = $this->getFileReader($file);
        if (is_null($this->fileReader)) {
            return false;
        }
        return true;
    }

    public function getNextItem()
    {
        if (is_null($this->fileReader)) {
            return null;
        }
        return $this->fileReader->getNextItem();
    }

    private function getFileReader(\SplFileObject $file): ?IFileReader
    {
        $input_format = $file->getExtension();
        if ($input_format == 'csv') {
            return new CSVReader($file);
        }

        throw new \InvalidArgumentException('Unsupported type of input file');
    }
}
