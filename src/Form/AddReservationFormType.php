<?php

namespace App\Form;

use App\Entity\Cities;
use App\Entity\Guides;
use App\Entity\Reservations;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $selectedCities = $options['selected_cities']; // 

        $builder
            ->add('day', DateType::class, [
                'widget' => 'single_text', 
                'label' => 'Jour :'
            ])
            ->add('begin', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Début :'
            ])
            ->add('end', TimeType::class, [
                'widget' => 'single_text',
                'label' => 'Fin :'
            ])
            ->add('meal', CheckboxType::class, [
                'label' => 'oui',
                'required' => false,
                'mapped' => true,
                'value' => 'oui',
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix :',
            ])
            ->add('city', EntityType::class, [
                'class' => Cities::class,  // Entité des villes
                'choice_label' => 'name',  // Afficher le nom de la ville dans le select
                'label' => 'Ville :',
                'choices' => $selectedCities,  // Restreindre les options aux villes sélectionnées
                'placeholder' => 'Choisissez une ville',
                'required' => true,
            ])
            ->add('address', TextType::class,[ 
            'label' => 'Point de rencontre (adresse) :',
            'attr' => [
                'placeholder' => 'Rechercher une adresse',
                'id' => 'address',
                'autocomplete' => 'off',
            ]]);
            // ->add('guide', EntityType::class, [
            //     'class' => Guides::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('users', EntityType::class, [
            //     'class' => Users::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservations::class,
            'selected_cities' => [], 
        ]);
    }
}
