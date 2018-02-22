<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 21.2.18
 * Time: 10.06
 */

namespace App\Service\EntityConverter\EntityToArrayConverters;

use App\Service\EntityConverter\IEntityToArrayConverter;

class ProductDataToArrayConverter implements IEntityToArrayConverter
{
    public function convertEntityToArray(object $entity): array
    {
        $item = [];
        $item['Product Code'] = $entity->getProductCode();
        $item['Product Name'] = $entity->getProductName();
        $item['Product Description'] = $entity->getProductDesc();
        $item['Stock'] = $entity->getProductStock();
        $item['Cost in GBP'] = $entity->getProductCost();
        if (!is_null($entity->getDiscontinuedDate())) {
            $item['Discontinued'] = 'yes';
        } else {
            $item['Discontinued'] = null;
        }

        return $item;
    }
}
