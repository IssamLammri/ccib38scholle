<?php

namespace App\Form;

use App\Entity\Teacher;
use App\Form\DataTransformer\CommaSeparatedToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeacherType extends AbstractType
{
    private $transformer;

    public function __construct(CommaSeparatedToArrayTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter last name']
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter first name']
            ])
            ->add('email', TextType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter email']
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'Phone Number',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter phone number']
            ])
            ->add('educationLevel', TextType::class, [
                'label' => 'Education Level',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter education level']
            ])
            ->add('specialities', TextType::class, [
                'label' => 'Specialities',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter specialities, separated by commas']
            ]);

        // Apply the transformer to the specialities field
        $builder->get('specialities')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Teacher::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
