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
 * @Route("/admin/option/product", name="admin_option_product")
 */
class ProductAvailableOptionController extends BaseAdminController
{
    /**
     * @Route("/show/{productId}", name="_option_product_show", methods="GET")
     */
    public function showOptionsProduct(int $productId): Response
    {
        return $this->render(
            'product/product-option-tab',
            [
                'product_id' => $productId
            ]
        );
    }

    /**
     * @Route("/set", name="_option_product_set", methods="POST")
     *
     */
    public function setOptionProduct(OptionProduct $optionProductService): Response
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
            $force = $request->get('force');

            if (!$optionProductId || !$productId || $force === null) {
                return $this->pageNotFound();
            }

            $optionProductService->deleteOptionOnProduct($optionProductId, $productId,
                OptionProduct::ADDED_BY_PRODUCT, $force);

        } catch (Exception $ex) {
            Tlog::getInstance()->addError($ex->getMessage());
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/products/update', [
            "current_tab" => "product_option_tab",
            "product_id" => $productId ?? null
        ]));
    }
}