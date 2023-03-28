<?php

namespace Option\Event;

use Thelia\Core\Event\Product\ProductCreateEvent;

class OptionProductCreateEvent extends ProductCreateEvent
{
    /** @var bool */
    protected bool $isOption;

    /**
     * @return bool|null
     */
    public function isOption(): ?bool
    {
        return $this->isOption;
    }

    /**
     * @param bool $isOption
     */
    public function setIsOption(bool $isOption): OptionProductCreateEvent
    {
        $this->isOption = $isOption;
        return $this;
    }
}