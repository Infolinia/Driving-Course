<?php

namespace AppBundle\Form;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SearchHolidayType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dt = new DateTime("now");
        $dt2 = new DateTime("now");
        $builder
            ->add('start_time', DateType::class, [
                    'widget' => 'single_text',
                    'data' => $dt->modify('+1 day'),
                    'html5' => true]
            )
            ->add('finish_time', DateType::class,[
                    'widget' => 'single_text',
                    'data' => $dt2->modify('+14 day'),
                    'html5' => true]
            )
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => null,
            'translation_domain' => false
        ));
    }
}