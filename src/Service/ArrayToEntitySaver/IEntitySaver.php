<?php

namespace App\Service\ArrayToEntitySaver;

interface IEntitySaver
{
    public function saveItemsArrayIntoEntity(array $items): void;

    public function getFailedRecords(): array;

    public function getAmountFailedInserts(): int;

    public function getAmountSuccessfulInserts(): int;
}
