<?php

namespace AppBundle\Form;

use AppBundle\Entity\Details;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ( $options['show']) {
            $builder
                ->add('details', DetailsType::class, array(
                    'data_class' => Details::class
                ))
                ->add('roles', ChoiceType::class, [
                    'choices' => array(
                        'ROLE_ADMIN' => 'ROLE_ADMIN',
                        'ROLE_INSTRUCTOR' => 'ROLE_INSTRUCTOR',
                        'ROLE_PARTICIPANT' => 'ROLE_PARTICIPANT'
                    ),
                    'data' => $options['roles'],
                ])
                ->add('submit', SubmitType::class);
        } else {
            $builder
                ->add('details', DetailsType::class, array(
                    'data_class' => Details::class
                ))
                ->add('submit', SubmitType::class);
        }
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'roles',
            'show',
        ));

        $resolver->setDefaults(array(
            'show'=> 0,
            'translation_domain' => false
        ));

    }

}