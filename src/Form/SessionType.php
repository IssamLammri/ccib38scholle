<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\Session;
use App\Entity\StudyClass;
use App\Entity\Teacher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Start Time',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'jj-mm-aaaa hh:mm' // Placeholder for date format
                ],
                'format' => 'dd-MM-yyyy HH:mm', // Custom format for date and time
                'html5' => false, // Disable HTML5 to use the custom format
            ])
            ->add('endTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'End Time',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'jj-mm-aaaa hh:mm' // Placeholder for date format
                ],
                'format' => 'dd-MM-yyyy HH:mm', // Custom format for date and time
                'html5' => false, // Disable HTML5 to use the custom format
            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'name',
                'label' => 'Room',
                'attr' => ['class' => 'form-control']
            ])
            ->add('studyClass', EntityType::class, [
                'class' => StudyClass::class,
                'label' => 'Study Class',
                'attr' => ['class' => 'form-control']
            ])
            ->add('teacher', EntityType::class, [
                'class' => Teacher::class,
                'choice_label' => 'lastName',
                'label' => 'Teacher',
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}