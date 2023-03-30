<?php

namespace Option\Event;

use Option\Model\OptionProduct;
use Thelia\Core\Event\ActionEvent;
use Thelia\Model\Product;

class CheckOptionEvent extends ActionEvent
{
    public const OPTION_CHECK_IS_VALID = 'option_check_is_valid';

    /** @var bool */
    protected bool $isValid;

    /** @var Product */
    protected Product $product;

    /** @var OptionProduct[] */
    protected array $options;

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     * @return CheckOptionEvent
     */
    public function setProduct(Product $product): CheckOptionEvent
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @param OptionProduct[] $options
     * @return CheckOptionEvent
     */
    public function setOptions(array $options = []): CheckOptionEvent
    {
        $this->options = $options;
        return $this;
    }
    
    /**
     * @return array|null
     */
    public function getOptions(): array|null
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     * @return CheckOptionEvent
     */
    public function setIsValid(bool $isValid): CheckOptionEvent
    {
        $this->isValid = $isValid;
        return $this;
    }
}