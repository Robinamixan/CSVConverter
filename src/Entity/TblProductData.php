<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TblProductDataRepository")
 */
class TblProductData
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="intProductDataId")
     */
    private $intProductDataId;

    /**
     * @ORM\Column(type="string", name="strProductName", length=50, nullable=false)
     */
    private $strProductName;

    /**
     * @ORM\Column(type="string", name="strProductDesc", length=255, nullable=false)
     */
    private $strProductDesc;

    /**
     * @ORM\Column(type="string", name="strProductCode", length=10, nullable=false, unique=true)
     */
    private $strProductCode;

    /**
     * @ORM\Column(type="integer", name="intProductStock", nullable=false)
     */
    private $intProductStock;

    /**
     * @ORM\Column(type="float", name="floatProductCost", nullable=false)
     */
    private $floatProductCost;

    /**
     * @ORM\Column(type="datetime", name="dtmAdded", nullable=true, options={"default" : null})
     */
    private $dtmAdded;

    /**
     * @ORM\Column(type="datetime", name="dtmDiscontinued", nullable=true, options={"default" : null})
     */
    private $dtmDiscontinued;

    /**
     * @ORM\Column(type="datetime", name="stmTimestamp", nullable=false)
     * @ORM\Version
     */
    private $stmTimestamp;

    /**
     * @return mixed
     */
    public function getIntProductDataId()
    {
        return $this->intProductDataId;
    }

    /**
     * @return mixed
     */
    public function getStrProductName()
    {
        return $this->strProductName;
    }

    /**
     * @param mixed $strProductName
     */
    public function setStrProductName(string $strProductName): void
    {
        $this->strProductName = $strProductName;
    }

    /**
     * @return mixed
     */
    public function getStrProductDesc()
    {
        return $this->strProductDesc;
    }

    /**
     * @param mixed $strProductDesc
     */
    public function setStrProductDesc(string $strProductDesc): void
    {
        $this->strProductDesc = $strProductDesc;
    }

    /**
     * @return mixed
     */
    public function getStrProductCode()
    {
        return $this->strProductCode;
    }

    /**
     * @param mixed $strProductCode
     */
    public function setStrProductCode(string $strProductCode): void
    {
        $this->strProductCode = $strProductCode;
    }

    /**
     * @return mixed
     */
    public function getDtmAdded()
    {
        return $this->dtmAdded;
    }

    /**
     * @param mixed $dtmAdded
     */
    public function setDtmAdded(\DateTime $dtmAdded): void
    {
        $this->dtmAdded = $dtmAdded;
    }

    /**
     * @return mixed
     */
    public function getDtmDiscontinued()
    {
        return $this->dtmDiscontinued;
    }

    /**
     * @param mixed $dtmDiscontinued
     */
    public function setDtmDiscontinued(\DateTime $dtmDiscontinued): void
    {
        $this->dtmDiscontinued = $dtmDiscontinued;
    }

    /**
     * @return mixed
     */
    public function getStmTimestamp()
    {
        return $this->stmTimestamp;
    }

    /**
     * @param mixed $stmTimestamp
     */
    public function setStmTimestamp($stmTimestamp): void
    {
        $this->stmTimestamp = $stmTimestamp;
    }

    /**
     * @return mixed
     */
    public function getIntProductStock()
    {
        return $this->intProductStock;
    }

    /**
     * @param mixed $intProductStock
     */
    public function setIntProductStock($intProductStock): void
    {
        $this->intProductStock = $intProductStock;
    }

    /**
     * @return mixed
     */
    public function getFloatProductCost()
    {
        return $this->floatProductCost;
    }

    /**
     * @param mixed $floatProductCost
     */
    public function setFloatProductCost($floatProductCost): void
    {
        $this->floatProductCost = $floatProductCost;
    }
}
