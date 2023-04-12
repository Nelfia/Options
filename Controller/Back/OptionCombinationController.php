<?php

namespace Option\Controller\Back;

use Exception;
use Option\Form\ProductAvailableOptionForm;
use Option\Service\OptionProduct;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/option/combination", name="admin_option_combination")
 */
class OptionCombinationController extends BaseAdminController
{
    /**
     * @Route("/list{productId}{option_product_id}", name="_option_combinations_show", methods="GET")
     */
    public function showOptionCombinationsOnProduct(Request $request): Response
    {
        $productId = $request->get('product_id');
        $optionProductId = $request->get('option_product_id');

        return $this->render(
            "option-combination/option-combination",
            [
                'product_id' => $productId,
                'option_product_id' => $optionProductId
            ]
        );
    }

    /**
     * @Route("/set", name="_option_combination_set", methods="POST")
     *
     */
    public function setOptionCombination(OptionProduct $optionProductService): Response
    {
        $form = $this->createForm(ProductAvailableOptionForm::class);

        try {
            $viewForm = $this->validateForm($form);
            $data = $viewForm->getData();

            $optionProductService->setOptionOnProduct($data['product_id'], $data['option_id'], $optionProductService::ADDED_BY_PRODUCT);

            return $this->generateSuccessRedirect($form);
        } catch (Exception $ex) {
            $errorMessage = $ex->getMessage();

            Tlog::getInstance()->error("Failed to validate product option form: $errorMessage");
        }

        $this->setupFormErrorContext(
            'Failed to process product option tab form data',
            $errorMessage,
            $form
        );

        return $this->generateErrorRedirect($form);
    }

    /**
     * @Route("/combination/{productid}{productOptionId}", name="_option_product_combination", methods="GET")
     */
    public function newOptionProductCombination(Request $request): Response
    {
        $productId = $request->get('product_id');
        $optionProductId = $request->get('option_product_id');

        return $this->render(
            "option-combination/option-combination",
            [
                'product_id' => $productId,
                'option_product_id' => $optionProductId
            ]
        );
    }

    /**
     * @Route("/delete", name="_option_product_delete", methods="GET")
     */
    public function deleteOptionProduct(
        Request       $request,
        OptionProduct $optionProductService
    ): Response
    {
        try {
            $optionProductId = $request->get('option_product_id');
            $productId = $request->get('product_id');

            if (!$optionProductId || !$productId) {
                return $this->pageNotFound();
            }

            $optionProductService->deleteOptionOnProduct($optionProductId, $productId);

        } catch (Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/products/update', [
            "current_tab" => "product_option_tab",
            "product_id" => $productId ?? null
        ]));
    }
}