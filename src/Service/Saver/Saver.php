<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 13.2.18
 * Time: 18.32
 */

namespace App\Service\Saver;


use App\Entity\TblProductData;
use App\Service\Saver\Savers\TblProductDataSaver;
use App\Service\Saver\Savers\TblProductDataTestSaver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;

class Saver
{
    private $entityManager;
    private $saver;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->saver = null;
    }

    public function saveArrayIntoEntity(array $contain, iSaver $saver)
    {
        $this->saver = $saver;
        $this->saver->saveArrayIntoEntity($contain);
    }

    public function getFailedRecords():array
    {
        return $this->saver->getFailedRecords();
    }

    public function getAmountFailedInserts(): int
    {
        return $this->saver->getAmountFailedInserts();
    }

    public function getAmountSuccessfulRecords(): int
    {
        return $this->saver->getAmountSuccessfulRecords();
    }

    public function getAmountProcessedRecords(): int
    {
        return $this->saver->getAmountProcessedRecords();
    }
}