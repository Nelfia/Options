<?php

namespace Option\Hook\Back;

use Option\Model\OptionProductQuery;
use Option\Model\ProductAvailableOptionQuery;
use Option\Service\Option;
use Option\Service\OptionProduct;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Assets\AssetResolverInterface;
use Thelia\Model\Base\ProductQuery;
use Thelia\Model\Base\TemplateQuery;
use TheliaSmarty\Template\SmartyParser;

class TemplateEditHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            "template-edit.bottom" => [
                [
                    "type" => "back",
                    "method" => "onTemplateEditBottom"
                ]
            ],
            "template.edit-js" => [
                [
                    "type" => "back",
                    "method" => "onTemplateEditJs"
                ]
            ]
        ];
    }

    /**
     * @throws PropelException
     */
    public function onTemplateEditBottom(HookRenderEvent $event): void
    {
        $templateId = $event->getArgument('template_id');
        $templateProducts = TemplateQuery::create()->findPk($templateId)->getProducts();
        $templateOptionIds = [];
        foreach ($templateProducts as $templateProduct){
            $templateOptions = ProductAvailableOptionQuery::create()
                ->filterByProductId($templateProduct->getId())
                ->groupByOptionId()
                ->find();
            foreach ($templateOptions as $templateOption){
                if(!in_array($templateOption->getOptionId(), $templateOptionIds, true)) {
                    $templateOptionIds[] = $templateOption->getOptionId();
                }
            }
        }
        $templateOptionProducts = [];
        foreach ($templateOptionIds as $templateOptionId){
            $templateOptionProductId = OptionProductQuery::create()->findPk($templateOptionId)->getProductId();
            $templateOptionProducts[$templateOptionId] = ProductQuery::create()->findPk($templateOptionProductId);
        }
        $availableOptions = OptionProductQuery::create()->find();

        $event->add($this->render(
            'template/template-edit.bottom.html',
            $event->getArguments() + [
                'options' => $availableOptions,
                'template_id' => $templateId,
                'template_option_products' => $templateOptionProducts
            ]
        ));
    }

    public function onTemplateEditJs(HookRenderEvent $event): void
    {
        $event->add($this->render(
            'template/template.edit-js.html',
            $event->getArguments()
        ));
    }

}