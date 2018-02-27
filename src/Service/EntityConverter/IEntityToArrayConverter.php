<?php

namespace App\Service\EntityConverter;

interface IEntityToArrayConverter
{
    public function convertEntityToArray(object $entity): array;
}
