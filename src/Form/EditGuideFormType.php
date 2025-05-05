<?php

namespace App\Form;

use App\Entity\Cities;
use App\Entity\Guides;
use App\Entity\Specialities;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditGuideFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nb_places',)
            ->add('languages')
            ->add('smoking', CheckboxType::class, [
                'label'    => 'oui', // Libellé du champ
                'required' => false, 
                'mapped' => true,   
                'value' => 'oui',         
            ])
            ->add('description')
            ->add('prefernces')
            ->add('speciality', EntityType::class, [
                'class' => Specialities::class,
                'choice_label' => 'name',
            ])
            ->add('cities', EntityType::class, [
                'class' => Cities::class,
                'choice_label' => 'name',    // Affiche le nom de la ville
                'multiple' => true,          // Permet de sélectionner plusieurs villes
                'expanded' => true,         // Utilise un select, pas des cases à cocher
                'required' => false,         // Facultatif
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Guides::class,
        ]);
    }
}
