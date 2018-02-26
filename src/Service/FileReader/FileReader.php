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

    public function loadFileToArray(\SplFileObject $file): array
    {
        $this->fileReader = $this->getFileReader($file->getExtension());

        return !is_null($this->fileReader) ? $this->fileReader->getFileContain($file) : [];
    }

    private function getFileReader(string $input_format): ?IFileReader
    {
        if ($input_format == 'csv') {
            return new CSVReader();
        } else {
            throw new \InvalidArgumentException('Unsupported type of input file');
        }
    }
}
