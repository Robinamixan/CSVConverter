<?php

namespace App\Service\EntityValidator;


interface IArrayToEntityValidator
{
    public function isValidItemToEntityRules(array $item): bool;
}
