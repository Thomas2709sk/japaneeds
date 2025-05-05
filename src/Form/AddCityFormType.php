<?php

namespace App\Form;

use App\Entity\Cities;
use App\Entity\Guides;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddCityFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [ 'label' => 'Ville :'])
            // ->add('slug')
            // // ->add('guides', EntityType::class, [
            // //     'class' => Guides::class,
            // //     'choice_label' => 'id',
            // //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cities::class,
        ]);
    }
}
