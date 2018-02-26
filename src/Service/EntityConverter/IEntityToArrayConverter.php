<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 21.2.18
 * Time: 9.54
 */

namespace App\Service\EntityConverter;

interface IEntityToArrayConverter
{
    public function convertEntityToArray(object $entity): array;
}
