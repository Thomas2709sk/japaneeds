<?php

namespace App\Controller\Guide;

use App\Controller\Guide\ReservationsController;
use App\Repository\ReservationsRepository;
use App\Entity\Users;
use App\Entity\Guides;
use App\Form\EditGuideFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/guide/account', name: 'app_guide_account_')]
class GuideAccountController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em, ReservationsRepository $reservationsRepository): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie si l'utilisateur est bien une instance de Users
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

        // Récupérer l'entité Guide associée à l'utilisateur connecté
        $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);

        // Si l'utilisateur n'est pas encore lié à un guide, créer un nouvel objet Guide
        if (!$guide) {
            $guide = new Guides();
            $guide->setUser($user); // Associe le guide à l'utilisateur
        }

        // Créer le formulaire basé sur l'entité Guide
        $accountForm = $this->createForm(EditGuideFormType::class, $guide);

        // Gérer la requête du formulaire
        $accountForm->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($accountForm->isSubmitted() && $accountForm->isValid()) {
            // Persister les changements dans la base de données
            $em->persist($guide);
            $em->flush();


            $this->addFlash('success', 'Vos informations de guide ont été mises à jour.');


            return $this->redirectToRoute('app_guide_account_index');
        }

        if ($guide) {
            // Récupérer les réservations associées au guide
            $reservations = $reservationsRepository->findBy(['guide' => $guide]);
        } else {
            $reservations = [];
        }


        // Retourner la vue avec le formulaire
        return $this->render('guide/guide_account/index.html.twig', [
            'guideForm' => $accountForm->createView(),
            'reservations' => $reservations,
        ]);
    }

}
