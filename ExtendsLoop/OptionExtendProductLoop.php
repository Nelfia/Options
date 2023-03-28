<?php

namespace Option\ExtendsLoop;

use Option\Model\Map\OptionProductTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Loop\LoopExtendsArgDefinitionsEvent;
use Thelia\Core\Event\Loop\LoopExtendsBuildModelCriteriaEvent;
use Thelia\Core\Event\Loop\LoopExtendsParseResultsEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

class OptionExtendProductLoop implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_ARG_DEFINITIONS, 'product') => ['optionArgDefinitions', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_BUILD_MODEL_CRITERIA, 'product') => ['optionBuildModelCriteria', 128],
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_PARSE_RESULTS, 'product') => ['optionParseResults', 128],
        ];
    }

    /**
     * @param LoopExtendsArgDefinitionsEvent $event
     */
    public function optionArgDefinitions(LoopExtendsArgDefinitionsEvent $event)
    {
        $argument = $event->getArgumentCollection();
        $argument->addArgument(Argument::createBooleanTypeArgument('only_option', false));
    }

    /**
     * @param LoopExtendsBuildModelCriteriaEvent $event
     */
    public function optionBuildModelCriteria(LoopExtendsBuildModelCriteriaEvent $event): void
    {
        /** @var ProductQuery $query */
        $query = $event->getModelCriteria();

        if ($event->getLoop()->getOnlyOption()) {
            $query->useOptionProductQuery()
                ->withColumn(OptionProductTableMap::COL_ID, 'option_id')
                ->endUse();
            return ;
        }

        $query->useOptionProductQuery("join_option_alias", Criteria::LEFT_JOIN)
            ->filterById(null, Criteria::ISNULL)
            ->endUse();
    }

    /**
     * @throws PropelException
     */
    public function optionParseResults(LoopExtendsParseResultsEvent $event): void
    {
        if ($event->getLoop()->getOnlyOption()) {
            $loopResult = $event->getLoopResult();
            foreach ($loopResult as $row) {
                /** @var Product $option */
                $option = $row->model;
                $row->set('OPTION_ID', $option->getVirtualColumn('option_id') !== null);
            }
        }
    }
}