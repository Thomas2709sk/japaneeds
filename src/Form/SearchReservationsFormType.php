<?php

namespace App\Form;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchReservationsFormType extends AbstractType
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

         // Récupérer les villes depuis la base de données
         $cities = $this->entityManager->getRepository('App\Entity\Cities')->findAll();

         // Construire une liste de choix pour le champ "city"
         $cityChoices = [];
         foreach ($cities as $city) {
             $cityChoices[$city->getName()] = $city->getName(); // Label => Valeur
         }

        $builder
        ->add('date', DateType::class, [
            'widget' => 'single_text',
            'required' => true,
            'label' => 'Date',
            'attr' => [
        'id' => 'custom_date_id', // Ajout de l'attribut id
    ],
        ])
        ->add('city', ChoiceType::class, [
            'choices' => $cityChoices, // Alimenter les choix
            'required' => true,
            'placeholder' => 'Choisissez une ville',
            'label' => 'Ville',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
