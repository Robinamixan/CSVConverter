<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 26.2.18
 * Time: 10.46
 */

namespace App\Tests\Service;

use App\Entity\Product;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityConverter\EntityToArrayConverters\ProductToArrayConverter;
use PHPUnit\Framework\TestCase;

class EntityConverterTest extends TestCase
{
    public function testArrayToEntityConvert()
    {
        $entityConverter = new EntityConverter();

        $item = [
            'product_name' => 'Bluray Player',
            'product_code' => 'P0004',
            'product_description' => 'Watch it in HD',
            'product_stock' => '10',
            'product_cost' => '24.55',
            'product_discontinued' => 'yes'
            ];

        $record = $entityConverter->convertArrayToEntity($item, new ArrayToProductConverter());
        $this->assertEquals('P0004', $record->getProductCode());
        $this->assertEquals(24.55, $record->getProductCost());
        $this->assertEquals(false, is_null($record->getDiscontinuedDate()));
    }

    public function testEntityToArratConvert()
    {
        $entityConverter = new EntityConverter();

        $item = [
            'product_name' => 'Bluray Player',
            'product_code' => 'P0004',
            'product_description' => 'Watch it in HD',
            'product_stock' => 10,
            'product_cost' => 24.55,
            'product_discontinued' => 'yes'
        ];

        $record = new Product();
        $record->setProductName('Bluray Player');
        $record->setProductCode('P0004');
        $record->setProductDesc('Watch it in HD');
        $record->setProductStock((int)10);
        $record->setProductCost((float)24.55);
        $record->setAddedDate(new \DateTime());
        $record->setDiscontinuedDate(new \DateTime());

        $resultArray = $entityConverter->convertEntityToArray($record, new ProductToArrayConverter());
        $this->assertEquals($item, $resultArray);
    }
}
