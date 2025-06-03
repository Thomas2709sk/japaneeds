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
        // get All the reviews
        $reviews = $reviewsRepository->findAll();
        return $this->render(
            'admin/reviews/index.html.twig',
            compact('reviews')
        );
    }

    // Confirm good review
    #[Route('/confirm/{id}', name: 'confirm', methods: ['POST'])]
    public function confirmGoodReview(Reviews $reviews, EntityManagerInterface $em, Security $security): Response
    {
        // check if User have 'ROLE_ADMIN'
        if (!$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut valider cet avis.');
        }

        // set Validate to true
        $reviews->setValidate(true);


        $em->persist($reviews);
        $em->flush();

        $this->addFlash('success', 'L\'avis a été validé et est maintenant visible par le guide.');

        return $this->redirectToRoute('app_admin_reviews_index');
    }

    #[Route('/confirm/bad/{id}', name: 'bad', methods: ['POST'])]
    public function confirmBadReview(Reviews $reviews, EntityManagerInterface $em, Security $security): Response
    {
        // check if User have 'ROLE_ADMIN'
        if (!$security->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Seul un administrateur peut valider cet avis.');
        }

        // set Validate to true
        $reviews->setValidate(true);

        // get the reservation associate to the review
        $reservation = $reviews->getReservation();
        if (!$reservation) {
            $this->addFlash('error', 'Aucune réservation associée à cet avis.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // Check the status of the reservation
        if ($reservation->getStatus() !== 'Vérification par la plateforme') {
            $this->addFlash('error', 'Cette réservation ne peut pas être confirmée.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // Count credits for the guide and admin
        $price = $reservation->getPrice();
        $platformFee = 2;
        $creditsForGuide = $price - $platformFee;

        $guide = $reservation->getGuide();

        // get Admin by its ID and role
        $admin = $em->getRepository(Users::class)->find(1);

        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles(), true)) {
            throw new \Exception('L\'utilisateur avec l\'ID 1 n\'est pas un administrateur.');
        }

        // get the guide by its ID and user ID
        $userGuide = $guide->getUser();

        // Give credits
            // for guide
        $userGuide->setCredits($userGuide->getCredits() + $creditsForGuide);

            // for admin
        if ($admin) {
            $admin->setCredits($admin->getCredits() + $platformFee);
        }

        // Update reservation status
        $reservation->setStatus('Confirmé');

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

    // Delete review
    #[Route('/remove/{id}', name: 'remove')]
    public function removeReviews(int $id, ReviewsRepository $reviewsRepository, EntityManagerInterface $em): Response
    {

        // check if User have 'ROLE_ADMIN'
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // get the review to delete by its ID
        $review = $reviewsRepository->find($id);

        // if review don't exist
        if (!$review) {
            $this->addFlash('error', 'Avis introuvable.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // delete review
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

        // check if User have 'ROLE_ADMIN'
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // get the review to delete by its ID
        $review = $reviewsRepository->find($id);

        // if review don't exist
        if (!$review) {
            $this->addFlash('error', 'Avis introuvable.');
            return $this->redirectToRoute('app_admin_reviews_index');
        }

        // remove review
        $em->remove($review);
        $em->flush();

        $this->addFlash('success', 'L\'avis a été supprimer avec succès.');

        return $this->redirectToRoute('app_admin_reviews_index');
    }
}
