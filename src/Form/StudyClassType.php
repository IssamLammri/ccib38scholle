<?php

namespace App\Form;

use App\Entity\StudyClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class StudyClassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Class Name',
                'attr' => ['placeholder' => 'Enter the name of the class']
            ])
            ->add('level', TextType::class, [
                'label' => 'Class Level',
                'attr' => ['placeholder' => 'Enter the level of the class']
            ])
            ->add('speciality', TextType::class, [
                'label' => 'Speciality',
                'attr' => ['placeholder' => 'Enter the speciality']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StudyClass::class,
        ]);
    }
}
