<?php
/**
 * Created by PhpStorm.
 * User: f.gorodkovets
 * Date: 26.2.18
 * Time: 13.51
 */

namespace App\Service\EntityValidator\ArrayToEntityValidators;

use App\Entity\Product;
use App\Service\EntityConverter\ArrayToEntityConverters\ArrayToProductConverter;
use App\Service\EntityConverter\EntityConverter;
use App\Service\EntityValidator\IArrayToEntityValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArrayToProductValidator implements IArrayToEntityValidator
{
    private $entityConverter;
    private $validator;

    public function __construct(EntityConverter $entityConverter, ValidatorInterface $validator)
    {
        $this->entityConverter = $entityConverter;
        $this->validator = $validator;
    }

    public function isValidItemToEntityRules(array $item): bool
    {
        if ($this->isValidItem($item)) {
            return true;
        }

        return false;
    }

    protected function isValidItem(array $item): bool
    {
        if (!$this->hasNeededField($item)) {
            return false;
        }

        if (!$this->hasNotEmptyFields($item)) {

            return false;
        }

        if (($item['product_discontinued'] !== 'yes') && (!is_null($item['product_discontinued']))) {
            if (is_null($item['product_discontinued'])) {
                return false;
            }
        }

        return true;
    }

    protected function hasNeededField(array $item): bool
    {
        if (!array_key_exists('product_name', $item)) {
            return false;
        }

        if (!array_key_exists('product_code', $item)) {
            return false;
        }

        if (!array_key_exists('product_description', $item)) {
            return false;
        }

        if (!array_key_exists('product_stock', $item)) {
            return false;
        }

        if (!array_key_exists('product_cost', $item)) {
            return false;
        }

        if (!array_key_exists('product_discontinued', $item)) {
            return false;
        }

        return true;
    }

    private function hasNotEmptyFields(array $item)
    {
        if ((empty($item['product_name'])) || (empty($item['product_code'])) || (empty($item['product_description']))) {
            return false;
        }

        if ((intval($item['product_stock']) === 0) || (empty($item['product_stock']))) {
            return false;
        }

        if ((floatval($item['product_cost']) === (float)0) || (empty($item['product_cost']))) {
            return false;
        }

        return true;
    }
}
