<?php

namespace Option\Form\Base;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

abstract class BaseOptionFrontForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add(
                'optionId',
                IntegerType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new NotBlank()
                    ]
                ]
            );
    }
}