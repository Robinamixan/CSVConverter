<?php

namespace App\Service\EntityConverter\ArrayToEntityConverters;

use App\Entity\Product;
use App\Service\EntityConverter\IArrayToEntityConverter;

class ArrayToProductConverter implements IArrayToEntityConverter
{
    public function convertArrayToEntity(array $item): Product
    {
        $productData = new Product();
        $productData->setProductName($item['product_name']);
        $productData->setProductCode($item['product_code']);
        $productData->setProductDesc($item['product_description']);
        $productData->setProductStock((int) $item['product_stock']);
        $productData->setProductCost((float) $item['product_cost']);
        $productData->setAddedDate(new \DateTime());
        if ($item['product_discontinued'] === 'yes') {
            $productData->setDiscontinuedDate(new \DateTime());
        }

        return $productData;
    }
}
