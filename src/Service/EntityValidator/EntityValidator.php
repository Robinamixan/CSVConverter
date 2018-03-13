<?php

namespace App\Service\EntityValidator;

class EntityValidator
{
    public function isValidItemToEntityRules(array $item, IArrayToEntityValidator $arrayToEntityValidator): bool
    {
        return $arrayToEntityValidator->isValidItemToEntityRules($item);
    }
}
