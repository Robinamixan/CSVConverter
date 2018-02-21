<?php

namespace App\Repository;

use App\Entity\TblProductData;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class TblProductDataRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TblProductData::class);
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('t')
            ->where('t.something = :value')->setParameter('value', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function existProductCode(string $code): bool
    {
        $record = $this->createQueryBuilder('t')
            ->where('t.strProductCode = :value')->setParameter('value', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
        if (is_null($record)) {
            return false;
        }
        return true;
    }

    public function createEntityFromArray(array $item): TblProductData
    {
        $productData = new TblProductData();
        $productData->setStrProductName($item['Product Name']);
        $productData->setStrProductCode($item['Product Code']);
        $productData->setStrProductDesc($item['Product Description']);
        $productData->setIntProductStock($item['Stock']);
        $productData->setFloatProductCost($item['Cost in GBP']);
        $productData->setDtmAdded(new \DateTime());

        if ($item['Discontinued'] == 'yes') {
            $productData->setDtmDiscontinued(new \DateTime());
        }
        return $productData;
    }

    public function createArrayFromEntity(TblProductData $productData): array
    {
        $item = [];
        $item['Product Code'] = $productData->getStrProductCode();
        $item['Product Name'] = $productData->getStrProductName();
        $item['Product Description'] = $productData->getStrProductDesc();
        $item['Stock'] = $productData->getIntProductStock();
        $item['Cost in GBP'] = $productData->getFloatProductCost();
        if (!is_null($productData->getDtmDiscontinued())) {
            $item['Discontinued'] = 'yes';
        } else {
            $item['Discontinued'] = null;
        }
        return $item;
    }
}
