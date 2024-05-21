<?php

namespace App\Form;

use App\Entity\AboutUs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class AboutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('contenu', TextareaType::class, [
                'attr' => ['class' => 'tinymce'],
            ])
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'about-us-image' => 'about-us-image',
                    'accueil-image' => 'accueil-image',
                    'section' => 'section'
                ],
            ])
            ->add('creer', SubmitType::class, [
                'label' => isset($options["label"]) ? $options["label"] : "Ajouter",
                'attr' => ['class' => 'btn btn-outline-success mt-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AboutUs::class,
        ]);
    }
}
