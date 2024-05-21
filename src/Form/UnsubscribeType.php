<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class UnsubscribeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('unsubscribeReason', TextType::class, [
                'label' => 'Reason for unsubscribing (optional)',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [ // Add the 'submit' field
                'label' => 'Unsubscribe', // Set custom label
                'attr' => [ // Set custom attributes (optional)
                    'class' => 'btn btn-primary'
                ]
         ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure form options here
        ]);
    }
}
