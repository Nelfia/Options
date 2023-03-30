<?php

namespace Option\Service\Front;

use Option\Model\OptionCartItemCustomization;
use Option\Model\OptionCartItemCustomizationQuery;
use Option\Model\OptionProduct;
use Option\Model\ProductAvailableOption;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\Option;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\CartItem;
use Thelia\Model\Product;
use Thelia\TaxEngine\Calculator;
use Thelia\TaxEngine\TaxEngine;

class OptionCartItemService
{
    protected ?Request $request;
    protected Option $optionService;
    protected TaxEngine $taxEngine;

    public function __construct(RequestStack $request, Option $optionService, TaxEngine $taxEngine)
    {
        $this->request = $request->getCurrentRequest();
        $this->optionService = $optionService;
        $this->taxEngine = $taxEngine;
    }

    /**
     * @param CartItem $cartItem
     * @throws PropelException
     */
    public function handleCartItemOptionPrice(CartItem $cartItem): void
    {
        $totalCustoms = $this->calculateTotalCustomPrice($cartItem);

        $cartItem
            ->setPrice((float)$cartItem->getPrice() + $totalCustoms['totalCustomizationPrice'])
            ->setPromoPrice((float)$cartItem->getPromoPrice() + $totalCustoms['totalCustomizationPrice'])
            ->save()
        ;
    }
    
    /**
     * @param CartItem $cartItem
     * @throws PropelException
     */
    public function removeCartItemOptionPrice(CartItem $cartItem): void
    {
        $totalCustoms = $this->calculateTotalCustomPrice($cartItem);
        
        $cartItem
            ->setPrice((float)$cartItem->getPrice() - $totalCustoms['totalCustomizationPrice'])
            ->setPromoPrice((float)$cartItem->getPromoPrice() - $totalCustoms['totalCustomizationPromoPrice'])
            ->save();
    }
    
    /**
     * @throws PropelException
     */
    public function calculateTotalCustomPrice(Cartitem $cartItem): array
    {
        $options = $this->getOptionsByCartItem($cartItem);
    
        // Calculate option HT price with cartItem tax rule.
        $taxCalculator = $this->getTaxCalculator($cartItem);
    
        $totalCustomizationPrice = 0;
        $totalCustomizationPromoPrice = 0;
    
        /** @var Product $option */
        foreach ($options as $option) {
            $totalCustomizationPrice += $taxCalculator->getUntaxedPrice($this->optionService->getOptionTaxedPrice($option));
            $totalCustomizationPromoPrice += $taxCalculator->getUntaxedPrice($this->optionService->getOptionTaxedPrice($option, true));
        }
        
        return [
            'totalCustomizationPrice' => $totalCustomizationPrice,
            'totalCustomizationPromoPrice' => $totalCustomizationPromoPrice,
        ];
    }
    
    /**
     * @throws PropelException
     */
    public function getOptionsByCartItem(CartItem $cartItem): array
    {
        $options = [];
        $productAvailableOptions = ProductAvailableOptionQuery::create()
            ->filterByProductId($cartItem->getProduct()->getId());

        /** @var ProductAvailableOption $productAvailableOption */
        foreach ($productAvailableOptions->find() as $productAvailableOption) {
            $options[] = $productAvailableOption->getOptionProduct()->getProduct();
        }

        return $options;
    }

    /**
     * @throws PropelException
     */
    public function persistCartItemCustomizationData(CartItem $cartItem, OptionProduct $optionProduct, array $formData): void
    {
        $productAvailableOption = ProductAvailableOptionQuery::create()
            ->filterByProductId($cartItem->getProductId())
            ->filterByOptionId($optionProduct->getId())
            ->findOne();
      
        if (null === $productAvailableOption) {
            return;
        }
        
        $customizationCartItemData = OptionCartItemCustomizationQuery::create()
            ->filterByProductAvailableOptionId($productAvailableOption->getId())
            ->filterByCartItemOptionId($cartItem->getId())->findOne();
        
        if (null !== $customizationCartItemData) {
            return;
        }
        
        $customizationCartItemData = new OptionCartItemCustomization();
        $customizationCartItemData
            ->setCartItemOptionId($cartItem->getId())
            ->setProductAvailableOptionId($productAvailableOption->getId());
        

        $fields = ['optionId', 'optionCode', 'error_message', 'success_url', 'error_url'];

        $customization = array_filter($formData, function ($key) use ($fields) {
            return !in_array($key, $fields);
        }, ARRAY_FILTER_USE_KEY);

        $taxCalculator = $this->getTaxCalculator($cartItem);
        $price = $this->optionService->getOptionTaxedPrice($optionProduct->getProduct());
        $untaxedPrice = $taxCalculator->getUntaxedPrice($price);

        $customizationCartItemData
            ->setPrice($untaxedPrice)
            ->setTaxedPrice($price)
            ->setCustomisationData(json_encode($customization))
            ->setQuantity($cartItem->getQuantity())
            ->save();
    }

    /**
     * @param CartItem $cartItem
     * @return Calculator
     * @throws PropelException
     */
    private function getTaxCalculator(CartItem $cartItem): Calculator
    {
        $taxCalculator = new Calculator();
        $taxCalculator->load($cartItem->getProduct(), $this->taxEngine->getDeliveryCountry(), $this->taxEngine->getDeliveryState());
        return $taxCalculator;
    }
}