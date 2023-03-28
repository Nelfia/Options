<?php

namespace Option\Event;

use Thelia\Core\Event\ActionEvent;
use Thelia\Model\CartItem;

class OptionInputValidationEvent extends ActionEvent
{
    public const CUSTOMIZATION_OPTION_INPUT_EXTEND = 'customization_option_input_extend';

    protected array $optionCustomizationFormData;

    protected int $optionId;

    protected CartItem $cartItem;

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function getOptionCustomizationFormData(): array
    {
        return $this->optionCustomizationFormData;
    }

    public function setOptionId(int $optionId): OptionInputValidationEvent
    {
        $this->optionId = $optionId;
        return $this;
    }

    public function setOptionCustomizationFormData(array $optionCustomizationFormData): OptionInputValidationEvent
    {
        $this->optionCustomizationFormData = $optionCustomizationFormData;
        return $this;
    }

    /**
     * @return CartItem
     */
    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }

    /**
     * @param CartItem $cartItem
     */
    public function setCartItem(CartItem $cartItem): OptionInputValidationEvent
    {
        $this->cartItem = $cartItem;
        return $this;
    }
}