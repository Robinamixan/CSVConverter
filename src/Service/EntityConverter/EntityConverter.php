<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 21.2.18
 * Time: 9.45
 */

namespace App\Service\EntityConverter;

use App\Entity\ProductData;

class EntityConverter
{
    public function convertArrayToEntity(array $item, IArrayToEntityConverter $arrayToEntityConverter): ProductData
    {
        return $arrayToEntityConverter->convertArrayToEntity($item);
    }

    public function convertEntityToArray(
        ProductData $productData,
        IEntityToArrayConverter $entityToArrayConverter
    ): array {
        return $entityToArrayConverter->convertEntityToArray($productData);
    }
}
