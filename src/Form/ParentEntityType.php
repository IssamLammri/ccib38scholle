<?php

namespace App\Form;

use App\Entity\ParentEntity;
use App\Entity\Student;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ParentEntityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fatherLastName', TextType::class, [
                'label' => 'Father Last Name',
                'attr' => ['placeholder' => 'Enter father\'s last name'],
            ])
            ->add('fatherFirstName', TextType::class, [
                'label' => 'Father First Name',
                'attr' => ['placeholder' => 'Enter father\'s first name'],
            ])
            ->add('fatherEmail', EmailType::class, [
                'label' => 'Father Email',
                'attr' => ['placeholder' => 'Enter father\'s email'],
            ])
            ->add('fatherPhone', TextType::class, [
                'label' => 'Father Phone',
                'attr' => ['placeholder' => 'Enter father\'s phone number'],
            ])
            ->add('motherLastName', TextType::class, [
                'label' => 'Mother Last Name',
                'attr' => ['placeholder' => 'Enter mother\'s last name'],
            ])
            ->add('motherFirstName', TextType::class, [
                'label' => 'Mother First Name',
                'attr' => ['placeholder' => 'Enter mother\'s first name'],
            ])
            ->add('motherEmail', EmailType::class, [
                'label' => 'Mother Email',
                'attr' => ['placeholder' => 'Enter mother\'s email'],
            ])
            ->add('motherPhone', TextType::class, [
                'label' => 'Mother Phone',
                'attr' => ['placeholder' => 'Enter mother\'s phone number'],
            ])
            ->add('familyStatus', ChoiceType::class, [
                'label' => 'Family Status',
                'choices'  => [
                    'Married' => 'married',
                    'Divorced' => 'divorced',
                ],
                'placeholder' => 'Select family status'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ParentEntity::class,
        ]);
    }
}
