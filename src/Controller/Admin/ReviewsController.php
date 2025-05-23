<?php

namespace App\Controller\Admin;

use App\Entity\Reviews;
use App\Entity\Users;
use App\Repository\ReviewsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reviews', name: 'app_admin_reviews_')]
class ReviewsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ReviewsRepository $reviewsRepository): Response
    {
        // Récupérez tous les avis
        $reviews = $reviewsRepository->findAll();
        return $this->render(
            'admin/reviews/index.html.twig',
            compact('reviews')
        );
    }

    #[Route('/confirm/{id}', name: 'confirm', methods: ['POST'])]
    public function confirmGoodReview(Reviews $reviews, EntityManagerInterface $em, Security $security): Response
    {
        // Vérifie que l'utilisateur connecté a le rôle ADMIN
        if (!$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut valider cet avis.');
        }

        // Met à jour l'état de l'avis pour indiquer qu'il est validé
        $reviews->setValidate(true);


        $em->persist($reviews);
        $em->flush();

        // Ajoute un message flash pour informer l'administrateur
        $this->addFlash('success', 'L\'avis a été validé et est maintenant visible par le guide.');

        // Redirige vers la liste des avis
        return $this->redirectToRoute('app_admin_reviews_index');
    }

    #[Route('/confirm/bad/{id}', name: 'bad', methods: ['POST'])]
    public function confirmBadReview(Reviews $reviews, EntityManagerInterface $em, Security $security): Response
    {
        // Vérifie que l'utilisateur connecté a le rôle ADMIN
        if (!$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut valider cet avis.');
        }

        // Met à jour l'état de l'avis pour indiquer qu'il est validé
        $reviews->setValidate(true);

        // Récupérer la réservation liée à l'avis
        $reservation = $reviews->getReservation();
        if (!$reservation) {
            $this->addFlash('error', 'Aucune réservation associée à cet avis.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // Vérifier que la réservation peut être confirmée
        if ($reservation->getStatus() !== 'Vérification par la plateforme') {
            $this->addFlash('error', 'Cette réservation ne peut pas être confirmée.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // Calculer les crédits pour le guide et l'admin
        $price = $reservation->getPrice();
        $platformFee = 2;
        $creditsForGuide = $price - $platformFee;

        $guide = $reservation->getGuide();

        // Rechercher l'admin
        $admin = $em->getRepository(Users::class)->find(1);

        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles(), true)) {
            throw new \Exception('L\'utilisateur avec l\'ID 1 n\'est pas un administrateur.');
        }

        // Récupérer l'utilisateur associé au Guide
        $userGuide = $guide->getUser();

        // Transférer les crédits
        // Ajouter au guide
        $userGuide->setCredits($userGuide->getCredits() + $creditsForGuide);

        // Ajouter à l'admin
        if ($admin) {
            $admin->setCredits($admin->getCredits() + $platformFee);
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus('Confirmé');

        // Sauvegarder les modifications
        $em->persist($reviews);
        $em->persist($guide);
        $em->persist($reservation);
        if ($admin) {
            $em->persist($admin);
        }

        $em->flush();

        $this->addFlash('success', 'L\'avis négatif a été validé, et les crédits ont été transférés.');

        return $this->redirectToRoute('app_admin_reviews_index');
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function removeReviews(int $id, ReviewsRepository $reviewsRepository, EntityManagerInterface $em): Response
    {

        // Vérifie si l'utilisateur connecté a le rôle ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer l'avis à supprimer
        $review = $reviewsRepository->find($id);

        // Vérifier si l'utilisateur existe
        if (!$review) {
            $this->addFlash('error', 'Avis introuvable.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // Supprimer l'avis
        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'L\'avis a été supprimer avec succès.');

        return $this->redirectToRoute('app_admin_reviews_index');
    }


    // A lier avec bouton mauvais avis 
    // Ajouter credits ?
    #[Route('/remove/bad/{id}', name: 'remove_bad')]
    public function removeBadReviews(int $id, ReviewsRepository $reviewsRepository, EntityManagerInterface $em): Response
    {

        // Vérifie si l'utilisateur connecté a le rôle ROLE_ADMIN
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Récupérer l'avis à supprimer
        $review = $reviewsRepository->find($id);

        // Vérifier si l'utilisateur existe
        if (!$review) {
            $this->addFlash('error', 'Avis introuvable.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // Supprimer l'avis
        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'L\'avis a été supprimer avec succès.');

        return $this->redirectToRoute('app_admin_reviews_index');
    }
}
