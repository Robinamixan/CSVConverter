<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 26.2.18
 * Time: 13.43
 */

namespace App\Service\EntityValidator;


use App\Entity\Product;

class EntityValidator
{
    public function isValidItemToEntityRules(array $item, IArrayToEntityValidator $arrayToEntityValidator): bool
    {
        return $arrayToEntityValidator->isValidItemToEntityRules($item);
    }
}
