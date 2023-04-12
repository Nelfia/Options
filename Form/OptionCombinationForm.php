<?php

namespace Option\Form;

use Option\Option;
use Propel\Runtime\Exception\PropelException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Thelia\Form\BaseForm;

class OptionCombinationForm extends BaseForm
{
    /**
     * @throws PropelException
     */
    protected function buildForm() : void
    {
        $this->formBuilder
            ->add(
                'category_id',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                ]
            )
            ->add(
                'option_id',
                ChoiceType::class,
                [
                    'required' => true,
                    'constraints' => [new NotBlank()],
                    'choices' => $this->getOptionChoices(),
                    'label' => $this->translator->trans('Options', [], Option::DOMAIN_NAME)
                ]
            );
    }

    public static function getName() : string
    {
        return "option_combination_form";
    }

//    /**
//     * @throws PropelException
//     */
//    protected function getOptionChoices(): array
//    {
//        $data = [];
//        $options = ProductQuery::create()->useOptionProductQuery()
//                ->withColumn(OptionProductTableMap::COL_ID,  'option_id')
//            ->endUse()
//            ->find();
//
//        foreach ($options as $option) {
//            $option->setLocale(Lang::getDefaultLanguage()->getLocale());
//            $data[$option->getTitle() . " - " . $option->getRef()] = $option->getVirtualColumn('option_id');
//        }
//
//        return $data;
//    }
}