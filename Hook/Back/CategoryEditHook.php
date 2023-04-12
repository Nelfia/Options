<?php

namespace Option\Hook\Back;

use Option\Option;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class CategoryEditHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            "category.tab" => [
                [
                    "type" => "back",
                    "method" => "onCategoryTab"
                ]
            ]
        ];
    }

    public function onCategoryTab(HookRenderBlockEvent $event): void
    {
        $event->add(
            [
                'id' => 'category_option_tab',
                'title' => $this->trans('Options', [], Option::DOMAIN_NAME),
                'href' => URL::getInstance()->absoluteUrl('/admin/option/category/show/' . $event->getArgument('id')),
                'content' => "Contenu !"
            ]
        );
    }

}