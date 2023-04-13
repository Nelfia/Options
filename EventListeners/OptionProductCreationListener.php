<?php

namespace Option\EventListeners;

use Option\Event\OptionProductCreateEvent;
use Option\Model\OptionProductQuery;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Product\ProductCreateEvent;
use Thelia\Core\Event\TheliaEvents;

class OptionProductCreationListener implements EventSubscriberInterface
{
    /**
     * @throws PropelException
     */
    public function createOption(ProductCreateEvent $event): void
    {
        if (!$event instanceof OptionProductCreateEvent || !$event->isOption()) {
            return;
        }

        OptionProductQuery::create()
            ->filterByProductId($event->getProduct()->getId())
            ->findOneOrCreate()
            ->save();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::PRODUCT_CREATE => ['createOption', 100],
        ];
    }
}