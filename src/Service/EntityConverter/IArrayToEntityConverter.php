<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 21.2.18
 * Time: 9.54
 */

namespace App\Service\EntityConverter;

interface IArrayToEntityConverter
{
    public function convertArrayToEntity(array $item);
}
