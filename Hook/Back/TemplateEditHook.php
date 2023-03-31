<?php

namespace Option\Hook\Back;

use Option\Model\OptionProductQuery;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

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

    public function onTemplateEditBottom(HookRenderEvent $event): void
    {
        $templateId = $event->getArgument('template_id');

        $availableOptions = OptionProductQuery::create()->find();

        $event->add($this->render(
            'template/template-edit.bottom.html',
            $event->getArguments() + [
                'options' => $availableOptions,
                'template_id' => $templateId
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