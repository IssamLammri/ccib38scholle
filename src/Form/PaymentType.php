<?php

namespace App\Form;

use App\Entity\Payment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parent')
            ->add('student')
            ->add('studyClass')
            ->add('amountPaid', MoneyType::class)
            ->add('paymentDate', DateType::class)
            ->add('paymentType', ChoiceType::class, [
                'choices' => [
                    'Mensuel' => 'mensuel',
                    'Annuel' => 'annuel',
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Valider le paiement']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Payment::class,
        ]);
    }
}
