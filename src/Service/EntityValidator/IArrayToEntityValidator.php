<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 26.2.18
 * Time: 13.44
 */

namespace App\Service\EntityValidator;


interface IArrayToEntityValidator
{
    public function isValidItemToEntityRules(array $item): bool;
}