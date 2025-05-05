<?php

namespace App\Form;

use App\Entity\Guides;
use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditAccountFormType extends AbstractType
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $user = $options['data'];  // L'utilisateur passé à la vue
        // Vérifier si l'utilisateur est déjà un guide dans la table Guides
        $isGuide = $this->em->getRepository(Guides::class)->findOneBy(['user' => $user]) !== null;


        $builder
            ->add('pseudo', TextType::class, [
                'required' => false,
            ])
            ->add('picture', FileType::class, [
                'required' => false,
                'mapped' => false,
                'label' => 'Photo de profil :',
                'attr' => [  
                        'accept' => 'image/png, image/jpeg, image/webp'       
                ]
            ])
            // ->add('becomeGuide', CheckboxType::class, [
            //     'label' => 'Devenir un guide',
            //     'mapped' => false,
            //     'required' => false,
            // ]);
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Voyageur' => 'voyageur',
                    'Guide' => 'guide',
                ],
                'label' => 'Rôle',
                'mapped' => false,// Ne pas mapper directement à l'entité Users
                 // Pré-sélectionner 'guide' si l'utilisateur est un guide
                 'data' => $isGuide ? 'guide' : 'voyageur',  // Si l'utilisateur est un guide, on sélectionne "guide"
            ]);
          
    
            // ->add('reservations', EntityType::class, [
            //     'class' => Reservations::class,
            //     'choice_label' => 'id',
            //     'multiple' => true,
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Users::class,
        ]);
    }
}
