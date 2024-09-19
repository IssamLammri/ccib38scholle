<?php

namespace App\Form;

use App\Entity\Room;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter room name']
            ])
            ->add('maxStudents', TextType::class, [
                'label' => 'Max Students',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter max students']
            ])
            ->add('comment', TextType::class, [
                'label' => 'Comment',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter comment']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
        ]);
    }
}
