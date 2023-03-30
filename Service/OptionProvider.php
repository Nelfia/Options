<?php

namespace Option\Service;

use Option\Event\OptionProductCreateEvent;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Thelia\Core\Event\Product\ProductUpdateEvent;
use Thelia\Core\Form\TheliaFormFactoryInterface;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\BaseForm;
use Thelia\Form\Definition\AdminForm;
use Thelia\Model\Country;
use Thelia\Model\Currency;
use Thelia\Model\Product;
use Thelia\Model\ProductPrice;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\TaxEngine\Calculator;

class OptionProvider
{
    public function __construct(
        protected TheliaFormFactoryInterface $theliaFormFactory,
        protected ParserContext              $parserContext
    )
    {

    }

    /**
     * @param array $formData
     * @return OptionProductCreateEvent
     */
    public function getCreationEvent(array $formData): OptionProductCreateEvent
    {
        return (new OptionProductCreateEvent())
            ->setRef($formData['ref'])
            ->setTitle($formData['title'])
            ->setLocale($formData['locale'])
            ->setDefaultCategory($formData['default_category'])
            ->setVisible($formData['visible'])
            ->setVirtual($formData['virtual'])
            ->setBasePrice($formData['price'])
            ->setBaseWeight($formData['weight'])
            ->setCurrencyId($formData['currency'])
            ->setTaxRuleId($formData['tax_rule'])
            ->setBaseQuantity($formData['quantity'])
            ->setTemplateId($formData['template_id'])
            ->setIsOption(true);
    }

    public function getUpdateEvent(array $formData): ProductUpdateEvent
    {
        return (new ProductUpdateEvent($formData['id']))
            ->setLocale($formData['locale'])
            ->setRef($formData['ref'])
            ->setTitle($formData['title'])
            ->setChapo($formData['chapo'])
            ->setDescription($formData['description'])
            ->setPostscriptum($formData['postscriptum'])
            ->setVisible($formData['visible'])
            ->setVirtual($formData['virtual'])
            ->setDefaultCategory($formData['default_category'])
            ->setBrandId($formData['brand_id'])
            ->setVirtualDocumentId($formData['virtual_document_id']);
    }

    /**
     * @throws PropelException
     */
    public function hydrateDefaultPseForm(Product $product, Currency $currentCurrency): BaseForm
    {
        $saleElement = ProductSaleElementsQuery::create()
            ->filterByProduct($product)
            ->filterByIsDefault(1)
            ->findOne();

        if (!$saleElement) {
            return $this->theliaFormFactory->createForm(AdminForm::PRODUCT_MODIFICATION);
        }

        $defaultCurrency = Currency::getDefaultCurrency();

        $productPrice = ProductPriceQuery::create()
            ->filterByCurrencyId($currentCurrency->getId())
            ->filterByProductSaleElements($saleElement)
            ->findOne();

        if ($productPrice === null) {
            $productPrice = new ProductPrice();

            if ($currentCurrency->getId() != $defaultCurrency->getId()) {
                $productPrice->setFromDefaultCurrency(true);
            }
        }

        $defaultPseData = [
            'product_sale_element_id' => $saleElement->getId(),
            'reference' => $saleElement->getRef(),
            'price' => $this->formatPrice($productPrice->getPrice()),
            'price_with_tax' => $this->formatPrice($this->computePrice($productPrice->getPrice(), $product)),
            'use_exchange_rate' => $productPrice->getFromDefaultCurrency() ? 1 : 0,
            'currency' => $productPrice->getCurrencyId(),
            'weight' => $saleElement->getWeight(),
            'quantity' => $saleElement->getQuantity(),
            'sale_price' => $this->formatPrice($productPrice->getPromoPrice()),
            'sale_price_with_tax' => $this->formatPrice($this->computePrice($productPrice->getPromoPrice(), $product)),
            'onsale' => $saleElement->getPromo() > 0 ? 1 : 0,
            'isnew' => $saleElement->getNewness() > 0 ? 1 : 0,
            'isdefault' => $saleElement->getIsDefault() > 0 ? 1 : 0,
            'ean_code' => $saleElement->getEanCode(),
        ];


        $defaultPseForm = $this->theliaFormFactory->createForm(AdminForm::PRODUCT_DEFAULT_SALE_ELEMENT_UPDATE, FormType::class, $defaultPseData);

        $this->parserContext->addForm($defaultPseForm);

        $data = [
            'id' => $product->getId(),
            'ref' => $product->getRef(),
            'locale' => $product->getLocale(),
            'title' => $product->getTitle(),
            'chapo' => $product->getChapo(),
            'description' => $product->getDescription(),
            'postscriptum' => $product->getPostscriptum(),
            'visible' => $product->getVisible(),
            'virtual' => $product->getVirtual(),
            'default_category' => $product->getDefaultCategoryId(),
            'brand_id' => $product->getBrandId(),
        ];

        return $this->theliaFormFactory->createForm(AdminForm::PRODUCT_MODIFICATION, FormType::class, $data, []);
    }

    /**
     * @throws PropelException
     */
    private function computePrice(mixed $price, Product $product): float
    {
        $calc = new Calculator();

        $calc->load(
            $product,
            Country::getShopLocation()
        );

        $return_price = $calc->getTaxedPrice($price);

        return (float)$return_price;
    }

    private function formatPrice(mixed $price): float
    {
        return (float)(number_format($price, 6, '.', ''));
    }
}