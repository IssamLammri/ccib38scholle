<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\Session;
use App\Entity\StudyClass;
use App\Entity\Teacher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startTime', null, [
                'widget' => 'single_text',
            ])
            ->add('endTime', null, [
                'widget' => 'single_text',
            ])
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choice_label' => 'id',
            ])
            ->add('studyClass', EntityType::class, [
                'class' => StudyClass::class,
                'choice_label' => 'id',
            ])
            ->add('teacher', EntityType::class, [
                'class' => Teacher::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
