<?php

namespace App\Service\EntityConverter\ArrayToEntityConverters;

use App\Entity\ProductData;
use App\Service\EntityConverter\IArrayToEntityConverter;

class ArrayToProductDataConverter implements IArrayToEntityConverter
{
    public function convertArrayToEntity(array $item): ProductData
    {
        $productData = new ProductData();
        $productData->setProductName($item['Product Name']);
        $productData->setProductCode($item['Product Code']);
        $productData->setProductDesc($item['Product Description']);
        $productData->setProductStock((int)$item['Stock']);
        $productData->setProductCost((float)$item['Cost in GBP']);
        $productData->setAddedDate(new \DateTime());
        if ($item['Discontinued'] === 'yes') {
            $productData->setDiscontinuedDate(new \DateTime());
        }

        return $productData;
    }
}
