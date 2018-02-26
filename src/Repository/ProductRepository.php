<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function productCodeExists(string $code): bool
    {
        $record = $this->createQueryBuilder('t')
            ->where('t.productCode = :value')->setParameter('value', $code)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return !empty($record);
    }
}
