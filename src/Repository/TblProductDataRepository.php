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
}
