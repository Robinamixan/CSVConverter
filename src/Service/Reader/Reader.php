<?php

namespace App\Service\Reader;

use App\Service\Reader\Readers\CSVReader;

class Reader
{
    private $failReport;
    private $reader;

    public function __construct()
    {
        $this->reader = null;
        $this->failReport = '';
    }

    public function loadFile(\SplFileObject $file): array
    {
        $this->reader = $this->getReader($file->getExtension());
        if (!is_null($this->reader)) {
            return $this->reader->getContain($file);
        } else {
            return [];
        }
    }

    private function getReader(string $input_format): ?iReader
    {
        if ($input_format == 'csv'){
            return new CSVReader();
        } else {
            $this->failReport = 'Unsupported type of input file';
            return null;
        }
    }

    public function getFailReport(): string
    {
        return $this->failReport;
    }
}