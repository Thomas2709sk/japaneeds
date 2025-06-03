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

    //  For good review
    #[Route('/create/{reservationId}', name: 'create', methods: ['POST'])]
    public function create(
        int $reservationId,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // find User
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour laisser un avis.');
        }

        // find reservation
        $reservation = $em->getRepository(Reservations::class)->find($reservationId);
        if (!$reservation) {
            $this->addFlash('error', 'La réservation est introuvable.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // if User is part of the reservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez pas laisser un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // if review from User already exist
        $existingReview = $em->getRepository(Reviews::class)->findOneBy([
            'reservation' => $reservation,
            'user' => $user,
        ]);
        if ($existingReview) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_reservations_index');
        }

        // if no review create a new Reviews object
        $review = new Reviews();
        $review->setUser($user);
        $review->setGuide($reservation->getGuide());
        $review->setReservation($reservation);

        // Create form
        $reviewForm = $this->createForm(ReviewsGuideFormType::class, $review);

    
        $reviewForm->handleRequest($request);
        if ($reviewForm->isSubmitted() && $reviewForm->isValid()) {
            $em->persist($review);
            $em->flush();

            $this->addFlash('success', 'Votre avis a été envoyé avec succès.');
            return $this->redirectToRoute('app_user_account_index');
        }


        return $this->render('user/reviews/index.html.twig', [
            'reviewForm' => $reviewForm,
            'reservation' => $reservation,
        ]);
    }

    //  For bad review
    #[Route('/reviews/bad/{reservationId}', name: 'bad',  methods: ['POST'])]
    public function createBadReview(
        int $reservationId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        // Find reservation
        $reservation = $em->getRepository(Reservations::class)->find($reservationId);
        if (!$reservation) {
            $this->addFlash('error', 'La réservation est introuvable.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // if User is part of the reservation
        $user = $this->getUser();
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez pas laisser un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

          // if review from User already exist
          $existingReview = $em->getRepository(Reviews::class)->findOneBy([
            'reservation' => $reservation,
            'user' => $user,
        ]);
        if ($existingReview) {
            $this->addFlash('error', 'Vous avez déjà laissé un avis pour cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // if no review create a new Reviews object
        $review = new Reviews();
        $review->setUser($user);
        $review->setGuide($reservation->getGuide());
        $review->setReservation($reservation);

        //  Create form
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
