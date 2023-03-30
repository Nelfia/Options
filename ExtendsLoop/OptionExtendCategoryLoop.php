<?php

namespace Option\ExtendsLoop;

use Exception;
use Option\Service\Option;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Loop\LoopExtendsBuildModelCriteriaEvent;
use Thelia\Core\Event\TheliaEvents;

class OptionExtendCategoryLoop implements EventSubscriberInterface
{
    public function __construct(protected Option $optionService)
    {
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'category')
                => ['optionCategoryBuildModelCriteria', 128]
        ];
    }

    /**
     * @param LoopExtendsBuildModelCriteriaEvent $event
     * @throws Exception
     */
    public function optionCategoryBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        $optionCategoryId = $this->optionService->getOptionCategory()?->getId();

        if (!$optionCategoryId) {
            return;
        }

        $event->getModelCriteria()->filterById($optionCategoryId, Criteria::NOT_IN);
    }
}