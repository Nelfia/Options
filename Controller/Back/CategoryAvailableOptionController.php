<?php

namespace Option\Controller\Back;

use Exception;
use Option\Model\ProductAvailableOptionQuery;
use Thelia\Model\Base\Product;
use Thelia\Model\Category;
use Option\Form\CategoryAvailableOptionForm;
use Option\Service\OptionProduct;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Log\Tlog;
use Thelia\Model\CategoryQuery;
use Thelia\Tools\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/option/category", name="admin_option_category")
 */
class CategoryAvailableOptionController extends BaseAdminController
{
    /** @Route("/show/{categoryId}", name="_option_category_show", methods="GET") */
    public function showCategoryOptionsProduct(int $categoryId): Response
    {
        return $this->render(
            'category/category-option-tab',
            [
                'category_id' => $categoryId
            ]
        );
    }

    /** @Route("/set", name="_option_category_set", methods="POST") */
    public function setOptionProductOnCategory(Request $request, OptionProduct $optionProductService): Response
    {
        $form = $this->createForm(CategoryAvailableOptionForm::class);

        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();
            $addAll = $data['add_all'];
            var_dump($addAll);
            die();
            $category = CategoryQuery::create()->findPk($data['category_id']);
            $optionProductService->setOptionOnCategoryProducts($category, $data['option_id']);

            return $this->generateSuccessRedirect($form);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate product option form: $errorMessage");
        }

        $this->setupFormErrorContext(
            'Failed to process category option tab form data',
            $errorMessage,
            $form
        );

        return $this->generateErrorRedirect($form);
    }

    /**
     * @Route("/delete", name="_option_category_delete", methods="GET")
     */
    public function deleteOptionProductOnCategory( Request $request, OptionProduct $optionProductService): Response
    {
        try {
            $optionProductId = $request->get('option_product_id');
            $categoryId = $request->get('category_id');
            $deleteAll = $request->get('delete_all');

            if (!$optionProductId || !$categoryId || $deleteAll === null) {
                return $this->pageNotFound();
            }

            $category = CategoryQuery::create()->findPk($categoryId);
            $optionProductService->deleteOptionOnCategoryTree($category, $optionProductId, $deleteAll);

        } catch (Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/categories/update', [
            "current_tab" => "category_option_tab",
            "category_id" => $categoryId ?? null
        ]));
    }

    /**
     * WIP - Lists category's products.
     * @Route("/test", name="_option_category_test", methods="GET")
     */
    public function test( Request $request ): Response
    {
        $categoryId = $request->get('category_id');
        $optionProductId = $request->get('option_product_id');
        $categoryProductsWithOption = $this->getProductsWithOptionOnCategory(CategoryQuery::create()->findPk
        ($categoryId), $optionProductId);

        return $this->render(
            'category/test',
            [
                'category_id' => $categoryId,
                'option_product_id' => $optionProductId,
                'category_option_product_ids' => $categoryProductsWithOption
            ]
        );
    }

    /**
     * Returns an array with the category's product wich have the specified option.
     *
     * @param Category $category
     * @param int|null $optionId
     * @return Product[]
     */
    private function getProductsWithOptionOnCategory(Category $category, ?int $optionId) : array
    {
        $productsWithOptionIds = [];
        $categoryProducts = $category->getProducts();

        $productsAvalaibleOption = ProductAvailableOptionQuery::create()->findByOptionId($optionId);
        foreach ($categoryProducts as $categoryProduct){
            foreach ($productsAvalaibleOption as $productAvailableOption){
                if($categoryProduct->getId() === $productAvailableOption->getProductId()){
                    $productsWithOptionIds[] = $categoryProduct->getId();
                }
            }
        }

        return $productsWithOptionIds;
    }

}