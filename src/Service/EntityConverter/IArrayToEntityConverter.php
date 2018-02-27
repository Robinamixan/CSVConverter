<?php

namespace App\Service\EntityConverter;

interface IArrayToEntityConverter
{
    public function convertArrayToEntity(array $item);
}
