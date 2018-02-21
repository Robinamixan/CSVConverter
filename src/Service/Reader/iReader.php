<?php
/**
 * Created by PhpStorm.
 * User: robinam
 * Date: 19.12.17
 * Time: 23:32
 */

namespace App\Service\Reader;

interface iReader
{
    public function getContain(\SplFileObject $file): array;
}