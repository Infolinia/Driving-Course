<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use AppBundle\Entity\Photo;
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

class InstructorCourseType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['role'] == 'ROLE_ADMIN') {
            if (!empty($options['instructors'])) {
                $builder->add('instructors', ChoiceType::class, [
                    'choices' => $options['instructors'],
                ])->add('submit', SubmitType::class);
            }
        }

        if ($options['role'] == 'ROLE_PARTICIPANT' and !$options['paid'] and !$options['is_full']) {
            $builder->add('instructorSelect',  ChoiceType::class, [
                'choices' => $options['instructorSelect'],
                'placeholder' => 'Wybierz instruktora',
            ])->add('pkk', TextType::class)
                ->add('submit', SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array(
            'instructors',
            'instructorSelect',
            'role',
        ));

        $resolver->setDefaults([
            'role' => "ROLE_USER",
            'paid' =>false,
            'is_full' =>false,
            'translation_domain' => false
        ]);
    }
}