<?php

namespace App\Service\EntityConverter;

use App\Entity\Product;

class EntityConverter
{
    public function convertArrayToEntity(array $item, IArrayToEntityConverter $arrayToEntityConverter): Product
    {
        return $arrayToEntityConverter->convertArrayToEntity($item);
    }

    public function convertEntityToArray(Product $productData, IEntityToArrayConverter $entityToArrayConverter): array
    {
        return $entityToArrayConverter->convertEntityToArray($productData);
    }
}
