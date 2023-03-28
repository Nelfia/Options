<?php

namespace Option\Service;

use Option\Model\OptionProductQuery;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Category;
use Thelia\Model\ProductQuery;

class OptionProduct
{
    /**
     * @throws PropelException
     */
    public function setOptionOnProduct(int $productId, int $optionId): void
    {
        $product = ProductQuery::create()->findPk($productId);
        $option = OptionProductQuery::create()->findPk($optionId);

        ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId())
            ->filterByOptionId($option->getId())
            ->findOneOrCreate()
            ->save();
    }

    /**
     * @throws PropelException
     */
    public function setOptionOnProductCategory(Category $category, int $optionId): void
    {
        foreach ($category->getProducts() as $product) {
            $this->setOptionOnProduct($product->getId(), $optionId);
        }
    }

    /**
     * @throws PropelException
     */
    public function deleteOptionOnProduct(int $optionProductId, int $productId): void
    {
        ProductAvailableOptionQuery::create()
            ->filterByOptionId($optionProductId)
            ->filterByProductId($productId)
            ->delete();
    }

    /**
     * @throws PropelException
     */
    public function deleteOptionOnProductCategory(Category $category, int $optionId): void
    {
        foreach ($category->getProducts() as $product) {
            $this->deleteOptionOnProduct($optionId, $product->getId());
        }
    }
}