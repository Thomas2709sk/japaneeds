<?php

namespace App\Form;

use App\Entity\Guides;
use App\Entity\Reservations;
use App\Entity\Reviews;
use App\Entity\Users;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReviewsGuideFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('rate',  HiddenType::class, [
                'label' => false,
                ])
            ->add('commentary', TextareaType::class, [
                'label' => 'Votre avis',
            ]);
            // ->add('user', EntityType::class, [
            //     'class' => Users::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('guide', EntityType::class, [
            //     'class' => Guides::class,
            //     'choice_label' => 'id',
            // ])
            // ->add('reservation', EntityType::class, [
            //     'class' => Reservations::class,
            //     'choice_label' => 'id',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reviews::class,
        ]);
    }
}
