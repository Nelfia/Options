<?php

namespace Option\EventListeners;

use JsonException;
use Option\Event\OptionProductCreateEvent;
use Option\Model\CategoryAvailableOptionQuery;
use Option\Service\OptionProduct;
use Option\Model\ProductAvailableOptionQuery;
use Option\Model\TemplateAvailableOptionQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Product\ProductCreateEvent;
use Thelia\Core\Event\TheliaEvents;

class ProductCreationListener implements EventSubscriberInterface
{
    private OptionProduct $optionProductService;

    public function __construct(OptionProduct $optionProductService){
        $this->optionProductService = $optionProductService;
    }

    /**
     * @throws PropelException|JsonException
     */
    public function addOptions(ProductCreateEvent $event): void
    {
        if ($event instanceof OptionProductCreateEvent) {
            return;
        }

        $newProduct = $event->getProduct();
        $newProductId = $newProduct->getId();

        $template = $newProduct->getTemplate();
        if($template) {
            $templateOptions = TemplateAvailableOptionQuery::create()->filterByTemplateId($template->getId())->find();
            foreach ($templateOptions as $templateOption){
                $this->optionProductService->setOptionOnProduct($newProductId, $templateOption->getOptionId(),
                    OptionProduct::ADDED_BY_TEMPLATE);
            }
        }
        $categories = $newProduct->getCategories();
        if($categories) {
            $categoriesOptions = [];
            foreach ($categories as $category) {
                var_dump($category->getId());
                $categoriesOptions[] = CategoryAvailableOptionQuery::create()->filterByCategoryId($category->getId())->find();

                if($categoriesOptions) {
                    $tabOptionIds = [];
                    foreach ($categoriesOptions as $categoriesOption) {
                        $tabOptionIds[] = $categoriesOption->getColumnValues('OptionId');
                    }
                    foreach ($tabOptionIds[0] as $optionId){
                        $this->optionProductService->setOptionOnProduct($newProductId, $optionId,
                            OptionProduct::ADDED_BY_CATEGORY);
                    }
                }
            }
        }

        ProductAvailableOptionQuery::create()
            ->filterByProductId($event->getProduct()->getId())
            ->findOneOrCreate()
            ->save();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_CREATE => ['addOptions', 50],
        ];
    }
}