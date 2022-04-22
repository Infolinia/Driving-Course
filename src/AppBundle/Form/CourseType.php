<?php

namespace AppBundle\Form;

use AppBundle\Entity\Category;
use AppBundle\Entity\Course;
use AppBundle\Entity\User;
use AppBundle\Entity\Details;
use AppBundle\Model\Contact;
use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Date;

class CourseType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('start_time', DateType::class, [
                    'widget' => 'single_text',
                    'html5' => true]
            )
            ->add('finish_time', DateType::class,[
                    'widget' => 'single_text',
                    'html5' => true]
            )
            ->add('description', TextareaType::class)
            ->add('price', IntegerType::class)
            ->add('maxParticipants', IntegerType::class)
            ->add('hours', IntegerType::class)
            ->add('submit', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Course::class,
            'translation_domain' => false
        ));
    }
}