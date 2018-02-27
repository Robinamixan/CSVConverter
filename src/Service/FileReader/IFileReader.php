<?php

namespace App\Service\FileReader;

interface IFileReader
{
    public function getNextItem(): ?array;
}
