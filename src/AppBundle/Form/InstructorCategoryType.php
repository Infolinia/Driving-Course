<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use AppBundle\Entity\News;
use AppBundle\Entity\User;
use AppBundle\Entity\Details;
use AppBundle\Model\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class InstructorCategoryType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!empty($options['categories'])) {
            $builder->add('categories', ChoiceType::class, [
                'choices' => $options['categories'],
            ])->add('submit', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'categories',
        ));

        $resolver->setDefaults(array(
            'translation_domain' => false
        ));
    }
}