<?php

namespace Option\Controller\Front;

use Exception;
use OpenApi\Exception\OpenApiException;
use OpenApi\Service\OpenApiService;
use OpenApi\Annotations as OA;
use OpenApi\Controller\Front\BaseFrontOpenApiController;
use OpenApi\Model\Api\ModelFactory;
use OpenApi\OpenApi;
use Option\Model\OptionProduct;
use Option\Service\CartItemCustomizationOptionHandler;
use Option\Service\Option;
use Propel\Runtime\Exception\PropelException;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\CartItemQuery;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Model\ProductQuery;

class OptionController extends BaseFrontOpenApiController
{
    /**
     * @Route("/open_api/option/customizations/{cartItemId}", name="option_customizations", methods="POST")
     *
     * @OA\Post(
     *     path="/open_api/option/customizations/{cartItemId}",
     *     tags={"Option"},
     *     summary="Set customization for a cart item",
     *     @OA\Parameter(
     *          name="cartItemId",
     *          description="The id of cart item",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="customizations",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="optionId",
     *                             type="string"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Success"
     *     )
     * )
     *
     * @throws OpenApiException
     */
    public function cartItemCustomizations(
        CartItemCustomizationOptionHandler $optionFormHandler,
        OpenApiService    $openApiService,
        ModelFactory      $modelFactory,
        Request           $request,
        int               $cartItemId,
    ): JsonResponse
    {
        try {
            if (null === $cartItem = CartItemQuery::create()->findPk($cartItemId)) {
                throw new Exception(Translator::getInstance()?->trans("Error, missing cart item parameter"));
            }

            $optionFormHandler->updateCustomizationOptionOnCartItem($cartItem);

            return OpenApiService::jsonResponse([
                'cart' => $openApiService->getCurrentOpenApiCart(),
                'cartItem' => $modelFactory->buildModel('CartItem', $request->getSession()?->getSessionCart()),
            ]);
        } catch (Exception $e) {
            throw $openApiService->buildOpenApiException(
                Translator::getInstance()?->trans('Invalid data', [], OpenApi::DOMAIN_NAME),
                Translator::getInstance()?->trans($e->getMessage(), [], OpenApi::DOMAIN_NAME)
            );
        }
    }

    /**
     * @Route("/open_api/option/customizations/get/{pseId}", name="get_option_customizations", methods="GET")
     * @OA\Get(
     *     path="/open_api/option/customizations/get/{pseId}",
     *     tags={"Option"},
     *     summary="Get option customization informations",
     *     @OA\Parameter(
     *          name="pseId",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *     )
     * )
     * @throws PropelException
     */
    public function getOptionsAvailable(
        Option $optionService,
        Request $request,
        int $pseId
    ): JsonResponse
    {
        $product = ProductQuery::create()
            ->useProductSaleElementsQuery()
                ->filterById($pseId)
            ->endUse()
        ->findOne();

        if (!$product) {
            return new JsonResponse([]);
        }

        $locale = $request->getSession()->getLang()->getLocale();

        $data = [];

        $options = $optionService->getProductAvailableOptions($product);

        /** @var OptionProduct $option */
        foreach ($options as $option) {
            $data[] = [
                'option_title' => $option->getProduct()->setLocale($locale)->getTitle(),
                'option_id' => $option->getId(),
                'option_code' => $option->getProduct()?->getRef(),
                'option_price' => $optionService->getOptionTaxedPrice($option->getProduct())
            ];
        }

        return new JsonResponse($data);
    }
}