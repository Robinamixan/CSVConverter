<?php
/**
 * Created by PhpStorm.
 * User: robinam
 * Date: 19.12.17
 * Time: 23:32
 */

namespace App\Service\FileReader;

interface IFileReader
{
    public function getNextItem(): ?array;
}
