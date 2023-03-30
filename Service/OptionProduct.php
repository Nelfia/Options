<?php

namespace Option\Service;

use OpenApi\Model\Api\Product;
use Option\Model\CategoryAvailableOptionQuery;
use Option\Model\OptionProductQuery;
use Option\Model\ProductAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\Template;

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
    public function setOptionOnCategory(int $categoryId, int $optionId): void
    {
        $category = CategoryQuery::create()->findPk($categoryId);
        $option = OptionProductQuery::create()->findPk($optionId);

        CategoryAvailableOptionQuery::create()
            ->filterByCategoryId($category->getId())
            ->filterByOptionId($option->getId())
            ->findOneOrCreate()
            ->save();
    }

    /**
     * @throws PropelException
     */
    public function setOptionOnCategoryProducts(Category $category, int $optionId): void
    {
        foreach ($category->getProducts() as $product) {
            $this->setOptionOnProduct($product->getId(), $optionId);
        }
    }

    /**
     * @throws PropelException
     */
    public function setOptionOnProductTemplate(Template $template, int $optionId): void
    {
        foreach ($template->getProducts() as $product) {
            $this->setOptionOnProduct($product->getId(), $optionId);
        }
    }

    /**
     * Lists the options available in the template products.
     *
     * @throws PropelException
     */
    public function getOptionProductsOnTemplate(Template $template) : ?array
    {
        $templateProducts = $template->getProducts();
        $templateOptionProducts = [];

        foreach ($templateProducts as $templateProduct){
            $templateOptionProducts[] = $this->getOptionProductsOnProduct($templateProduct);
        }

        return $templateOptionProducts;
    }

    public function getOptionProductsOnProduct(Product $product): ?array
    {
        $productAvalaibleOptions = ProductAvailableOptionQuery::create()
            ->findByProductId($product->getId());

        $optionIds = [];
        foreach ($productAvalaibleOptions as $productAvalaibleOption) {
            if (!in_array($productAvalaibleOption->getOptionId(), $optionIds, true)) {
                $optionIds[] = $productAvalaibleOption->getOptionId();
            }
        }

        $optionProductIds = [];
        foreach ($optionIds as $optionId){
            $optionProduct = OptionProductQuery::create()->findById($optionId);
            $optionProductIds[] = $optionProduct->get('product_id');
        }

        $optionProducts = [];
        foreach ($optionProductIds as $optionProductId){
            $optionProducts[] = ProductQuery::create()->findPk($optionProductId);
        }
        return $optionProducts;
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

    /**
     * @throws PropelException
     */
    public function deleteOptionOnProductTemplate(Template $template, int $optionId): void
    {
        foreach ($template->getProducts() as $product) {
            $this->deleteOptionOnProduct($optionId, $product->getId());
        }
    }
}