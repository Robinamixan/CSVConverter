<?php

namespace App\Service\Saver;

interface iSaver
{
    public function saveArrayIntoEntity(array $contain): void;

    public function getFailedRecords(): array;

    public function getAmountFailedInserts(): int;

    public function getAmountSuccessfulRecords(): int;

    public function getAmountProcessedRecords(): int;
}