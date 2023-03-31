<?php

namespace Option\Service;

use OpenApi\Model\Api\Product;
use Option\Model\CategoryAvailableOptionQuery;
use Option\Model\OptionProductQuery;
use Option\Model\ProductAvailableOptionQuery;
use Option\Model\TemplateAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\Template;

class OptionProduct
{
    public const ADDED_BY_PRODUCT = 1;
    public const ADDED_BY_CATEGORY = 2;
    public const ADDED_BY_TEMPLATE = 3;

    /**
     * Sets an option on a product.
     *
     * @param int $productId
     * @param int $optionId
     * @param int $added_by origin of the new added option
     * @return void
     * @throws PropelException
     */
    public function setOptionOnProduct(int $productId, int $optionId, int $addedBy = 1): void
    {
        $product = ProductQuery::create()->findPk($productId);
        $option = OptionProductQuery::create()->findPk($optionId);
        $curentProductAvailableOption = ProductAvailableOptionQuery::create()->filterByProductId($productId)
            ->filterByOptionId($optionId)->find();

        $newAddedBy = [];
        $curentAddedBy = $curentProductAvailableOption?->getColumnValues('OptionAddedBy');
        if($curentAddedBy) {
            foreach ($curentAddedBy[0] as $item) {
                if ($item !== $addedBy) {
                    $newAddedBy[] = $item;
                }
            }
        }
        $newAddedBy[] = $addedBy;

        ProductAvailableOptionQuery::create()
            ->filterByProductId($product->getId())
            ->filterByOptionId($option->getId())
            ->findOneOrCreate()
            ->setOptionAddedBy(json_encode($newAddedBy))
            ->save();
    }

    /**
     * Sets an option on Category's products.
     *
     * @param Category $category
     * @param int $optionId
     * @return void
     * @throws PropelException
     */
    public function setOptionOnCategoryProducts(Category $category, int $optionId): void
    {
        CategoryAvailableOptionQuery::create()
            ->filterByCategoryId($category->getId())
            ->filterByOptionId($optionId)
            ->findOneOrCreate()
            ->save();

        foreach ($category->getProducts() as $product) {
            $this->setOptionOnProduct($product->getId(), $optionId, self::ADDED_BY_CATEGORY);
        }
    }

    /**
     * Sets an option on Template's products.
     *
     * @param Template $template
     * @param int $optionId
     * @return void
     * @throws PropelException
     */
    public function setOptionOnTemplateProducts(Template $template, int $optionId): void
    {
       TemplateAvailableOptionQuery::create()
            ->filterByTemplateId($template->getId())
            ->filterByOptionId($optionId)
            ->findOneOrCreate()
            ->save();

        foreach ($template->getProducts() as $product) {
            $this->setOptionOnProduct($product->getId(), $optionId, self::ADDED_BY_TEMPLATE);
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

    /**
     * @param Product $product
     * @return array|null
     */
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
     * Removes an option according to its origin.
     *
     * Only options with a single origin (OptionAddedBy column) are completely deleted.
     * If an option has been added to the product by a category and a template, only the origin of the option that is
     * being deleted is removed.
     *
     * If you want to completely remove the option from the product, even if it has multiple origins, just pass the
     * optional parameter $force=true)
     *
     * @param int $optionId
     * @param int $productId
     * @param int $deletedBy
     * @param bool $force if TRUE, removes option totaly.
     * @return void
     * @throws PropelException|\JsonException
     */
    public function deleteOptionOnProduct(int $optionId, int $productId, int $deletedBy = 1, bool $force = false): void
    {
        $productAvailableOption = ProductAvailableOptionQuery::create()
            ->filterByOptionId($optionId)
            ->filterByProductId($productId)
            ->findOne();

        $addedBy = $productAvailableOption->getOptionAddedBy();
        if(!$force && count($addedBy) > 1){
            unset($addedBy[array_search($deletedBy, $addedBy, true)]);
            $productAvailableOption->setOptionAddedBy(json_encode($addedBy, JSON_THROW_ON_ERROR))->save();
        } else {
            $productAvailableOption->delete();
        }
    }

    /**
     * Removes an option on Category's products.
     *
     * @param Category $category
     * @param int $optionId
     * @return void
     * @throws PropelException|\JsonException
     */
    public function deleteOptionOnCategoryProducts(Category $category, int $optionId): void
    {
        CategoryAvailableOptionQuery::create()
            ->filterByOptionId($optionId)
            ->filterByCategoryId($category->getId())
            ->delete();

        foreach ($category->getProducts() as $product) {
            $this->deleteOptionOnProduct($optionId, $product->getId(), self::ADDED_BY_CATEGORY);
        }
    }

    /**
     * Removes an option on Template's products.
     *
     * @param Template $template
     * @param int $optionId
     * @return void
     * @throws PropelException|\JsonException
     */
    public function deleteOptionOnTemplateProducts(Template $template, int $optionId): void
    {
        TemplateAvailableOptionQuery::create()
            ->filterByOptionId($optionId)
            ->filterByTemplateId($template->getId())
            ->delete();

        foreach ($template->getProducts() as $product) {
            $this->deleteOptionOnProduct($optionId, $product->getId(), self::ADDED_BY_TEMPLATE);
        }
    }
}