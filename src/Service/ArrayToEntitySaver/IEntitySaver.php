<?php

namespace App\Service\ArrayToEntitySaver;

interface IEntitySaver
{
    public function saveArrayIntoEntity(array $contain): void;

    public function getFailedRecords(): array;

    public function getAmountFailedInserts(): int;

    public function getAmountSuccessfulRecords(): int;

    public function getAmountProcessedRecords(): int;
}
