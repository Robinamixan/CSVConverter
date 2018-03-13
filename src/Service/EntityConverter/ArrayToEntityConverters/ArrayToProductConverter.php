<?php

namespace App\Service\EntityConverter\ArrayToEntityConverters;

use App\Entity\Product;
use App\Service\EntityConverter\IArrayToEntityConverter;

class ArrayToProductConverter implements IArrayToEntityConverter
{
    public function convertArrayToEntity(array $item): Product
    {
        $date = new \DateTime();
        $productData = new Product();
        $productData->setProductName($item['product_name']);
        $productData->setProductCode($item['product_code']);
        $productData->setProductDesc($item['product_description']);
        $productData->setProductStock((int) $item['product_stock']);
        $productData->setProductCost((float) $item['product_cost']);
        $productData->setAddedDate($date);
        if ($item['product_discontinued'] === 'yes') {
            $productData->setDiscontinuedDate($date);
        }
        $item = null;
        $date = null;

        return $productData;
    }
}
