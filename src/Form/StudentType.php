<?php

namespace App\Form;

use App\Entity\ParentEntity;
use App\Entity\Student;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['placeholder' => 'Enter student\'s last name'],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => ['placeholder' => 'Enter student\'s first name'],
            ])
            ->add('birthDate', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Birth Date',
                'attr' => ['placeholder' => 'Enter birth date'],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Gender',
                'choices'  => [
                    'Male' => 'male',
                    'Female' => 'female',
                ],
                'placeholder' => 'Select gender'
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'attr' => ['placeholder' => 'Enter address'],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
                'attr' => ['placeholder' => 'Enter postal code'],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'attr' => ['placeholder' => 'Enter city'],
            ])
            ->add('level', TextType::class, [
                'label' => 'Level',
                'attr' => ['placeholder' => 'Enter student\'s level'],
            ])
            ->add('parent', EntityType::class, [
                'class' => ParentEntity::class,
                // Remove choice_label to use __toString() method automatically
                'label' => 'Parent',
                'placeholder' => 'Select a parent',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}
