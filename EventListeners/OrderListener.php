<?php

namespace Option\EventListeners;

use Option\Model\OptionCartItemCustomizationQuery;
use Option\Service\Front\OptionOrderProductService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\OrderProduct;

class OrderListener implements EventSubscriberInterface
{
    protected OptionOrderProductService $customizationOrderProductService;

    public function __construct(OptionOrderProductService $customizationOrderProductService)
    {
        $this->customizationOrderProductService = $customizationOrderProductService;
    }

    public function handleCustomization(OrderEvent $event)
    {
        $placedOrder = $event->getOrder();
        if (!$placedOrder) {
            return null;
        }

        $orderProducts = $placedOrder->getOrderProducts();

        /** @var OrderProduct $orderProduct */
        foreach ($orderProducts as $orderProduct) {
            $this->setOrderProductData($orderProduct);
            $this->customizationOrderProductService->handleOrderProduct($orderProduct);
        }
    }

    protected function setOrderProductData(OrderProduct $orderProduct)
    {
        $cartItemId = $orderProduct->getCartItemId();
        $customizations = OptionCartItemCustomizationQuery::create()
            ->filterByCartItemOptionId($cartItemId)
            ->find();

        foreach ($customizations as $customization) {
            $customization
                ->setOrderProductId($orderProduct->getId())
                ->save();
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            TheliaEvents::ORDER_BEFORE_PAYMENT => array('handleCustomization', 200)
        );
    }
}