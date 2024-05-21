<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType; // Import the FileType
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

class MailerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sendTo', ChoiceType::class, [
                'choices' => [
                    'All Clients' => 'all',
                    'Unpaid Clients' => 'unpaid',
                    'Subscriber' => 'subscriber',
                ],
                'attr' => [
                    'class' => 'form-control',
                    'onchange' => 'toggleFields()',
                ],
            ])
            ->add('template', ChoiceType::class, [
                'label' => 'Select Email Template',
                'choices' => [
                    'Default' => 'default',
                    'All Clients' => 'clients',
                    'Subscriber' => 'subscriber',
                    'Unpaid Clients' => 'unpaid',
                    // Add more template choices as needed
                ],
                'required' => true,
            ])
            ->add('subject', TextType::class)
            ->add('body', TextareaType::class)
            ->add('attachment', FileType::class, [ // Add the FileType for attachment
                'required' => false,
                'mapped' => false, // This field is not mapped to any entity property
                'label' => 'Attachment (PDF, Image)', // Custom label
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send Email',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
