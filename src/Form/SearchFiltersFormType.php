<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFiltersFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('price', NumberType::class, [
            'label' => 'Prix',
            'required' => false,
            'attr' => [
                'placeholder' => 'Entrez un prix',
            ],
        ])
        ->add('meal', ChoiceType::class, [
            'label' => 'Repas inclus ?',
            'required' => false,
            'choices' => [
                'Oui' => true,
                'Non' => false,
            ],
            'expanded' => true, // Rend le champ comme des boutons radio
            'multiple' => false, // Force un seul choix
        ])
        ->add('rate', ChoiceType::class, [
            'label' => 'Note',
            'required' => false,
            'choices' => [
                '1 étoile' => 1,
                '2 étoiles' => 2,
                '3 étoiles' => 3,
                '4 étoiles' => 4,
                '5 étoiles' => 5,
            ],
            'placeholder' => 'Choisissez une note',
            'required' => false,
        ])
        ->add('begin', TimeType::class, [
            'label' => 'Heure de début',
            'required' => false,
            'widget' => 'single_text', // Affiche un champ de saisie simple
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
             'method' => 'GET'
            // Configure your form options here
        ]);
    }
}
