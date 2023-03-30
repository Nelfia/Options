<?php

namespace Option\Service;

use Option\Event\OptionInputValidationEvent;
use Option\Model\OptionProductQuery;
use Option\Service\Front\OptionCartItemService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Form\TheliaFormFactoryInterface;
use Thelia\Core\Form\TheliaFormValidatorInterface;
use Thelia\Model\CartItem;

class CartItemCustomizationOptionHandler
{
    public function __construct(
        protected EventDispatcherInterface     $dispatcher,
        protected OptionCartItemService        $optionCartItemService,
        protected RequestStack                 $requestStack,
        protected TheliaFormFactoryInterface   $theliaFormFactory,
        protected TheliaFormValidatorInterface $theliaFormValidator
    )
    {
    }

    public function updateCustomizationOptionOnCartItem(CartItem $cartItem): void
    {
        if (!$optionCodes = $this->requestStack->getCurrentRequest()->get('optionCodes') ?? null) {
            return;
        }

        foreach ($optionCodes as $optionCode) {
            $form = $this->theliaFormFactory->createForm($optionCode, FormType::class, [], ['csrf_protection' => false]);

            $this->theliaFormValidator->validateForm($form);

            $optionId = $form->getForm()->get('optionId')->getData();
            $formData = $form->getForm()->getData();

            if (!$optionProduct = OptionProductQuery::create()->findPk($optionId)) {
                continue;
            }

            $extendEvent = (new OptionInputValidationEvent())
                ->setOptionId($optionId)
                ->setOptionCustomizationFormData($formData)
                ->setCartItem($cartItem);

            $this->dispatcher->dispatch($extendEvent, OptionInputValidationEvent::CUSTOMIZATION_OPTION_INPUT_EXTEND);

            $this->optionCartItemService->persistCartItemCustomizationData(
                $cartItem,
                $optionProduct,
                $extendEvent->getOptionCustomizationFormData()
            );

            $this->optionCartItemService->handleCartItemOptionPrice($cartItem);
        }
    }
}