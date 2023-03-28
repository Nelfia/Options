<?php

namespace Option\EventListeners;

use Option\Model\OptionCartItemCustomization;
use Option\Model\OptionCartItemCustomizationQuery;
use Option\Service\Front\OptionCartItemService;
use Propel\Runtime\Propel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Cart\CartItemDuplicationItem;
use Thelia\Core\Event\TheliaEvents;

class DuplicateCartItemListener implements EventSubscriberInterface
{
    private OptionCartItemService $optionCartItemService;

    public function __construct(OptionCartItemService $optionCartItemService)
    {
        $this->optionCartItemService = $optionCartItemService;
    }
    
    /**
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function duplicateOrderProductData(CartItemDuplicationItem $event)
    {
        Propel::disableInstancePooling();
        $options = OptionCartItemCustomizationQuery::create()
            ->filterByCartItemOptionId($event->getOldItem()->getId())
            ->find();

        /** @var  OptionCartItemCustomization[] $options */
        foreach ($options as $option) {
            $option
                ->setCartItemOptionId($event->getNewItem()->getId())
                ->save();
        }
        $this->optionCartItemService->handleCartItemOptionPrice($event->getNewItem());
    }

    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::CART_ITEM_DUPLICATE => ['duplicateOrderProductData', 64]
        );
    }
}