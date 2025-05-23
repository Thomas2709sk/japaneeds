<?php

namespace App\Controller\User;

use App\Entity\Reservations;
use App\Entity\Reviews;
use App\Form\ReviewsGuideFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/reviews', name: 'app_user_reviews_')]
class ReviewsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('user/reviews/index.html.twig', [
            'controller_name' => 'ReviewsController',
        ]);
    }

    #[Route('/create/{reservationId}', name: 'create', methods: ['POST'])]
    public function create(
        int $reservationId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour laisser un avis.');
        }

        // Trouver la réservation
        $reservation = $em->getRepository(Reservations::class)->find($reservationId);
        if (!$reservation) {
            $this->addFlash('error', 'La réservation est introuvable.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Vérifier si l'utilisateur a participé à cette réservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez pas laisser un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Vérifier si un avis existe déjà pour cette réservation
        $existingReview = $em->getRepository(Reviews::class)->findOneBy([
            'reservation' => $reservation,
            'user' => $user,
        ]);
        if ($existingReview) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_reservations_index');
        }

        // Créer un nouvel avis
        $review = new Reviews();
        $review->setUser($user);
        $review->setGuide($reservation->getGuide());
        $review->setReservation($reservation);

        // Créer le formulaire
        $reviewForm = $this->createForm(ReviewsGuideFormType::class, $review);

        // Gérer la soumission du formulaire
        $reviewForm->handleRequest($request);
        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Votre avis a été envoyé avec succès.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Retourner le formulaire au template
        return $this->render('user/reviews/index.html.twig', [
            'reviewForm' => $reviewForm,
            'reservation' => $reservation,
        ]);
    }
    #[Route('/reviews/bad/{reservationId}', name: 'bad')]
    public function createBadReview(
        int $reservationId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        // Récupérer la réservation
        $reservation = $em->getRepository(Reservations::class)->find($reservationId);
        if (!$reservation) {
            $this->addFlash('error', 'La réservation est introuvable.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Vérifier si l'utilisateur connecté peut laisser un avis
        $user = $this->getUser();
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez pas laisser un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

          // Vérifier si un avis existe déjà pour cette réservation
          $existingReview = $em->getRepository(Reviews::class)->findOneBy([
            'reservation' => $reservation,
            'user' => $user,
        ]);
        if ($existingReview) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        $review = new Reviews();
        $review->setUser($user);
        $review->setGuide($reservation->getGuide());
        $review->setReservation($reservation);

        $reviewForm = $this->createForm(ReviewsGuideFormType::class, $review);
        $reviewForm->handleRequest($request);

        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Votre avis a été enregistré avec succès.');
            return $this->redirectToRoute('app_user_account_index');
        }

        return $this->render('user/reviews/bad.html.twig', [
            'reviewForm' => $reviewForm->createView(),
            'reservation' => $reservation,
        ]);
    }
}
