<?php

namespace App\Controller\Guide;

use App\Repository\GuidesRepository;
use App\Repository\ReviewsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/guide/reviews', name: 'app_guide_reviews_')]
class ReviewsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ReviewsRepository $reviewsRepository, GuidesRepository $guidesRepository): Response
    {
        // Récupère l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer le guide associé à l'utilisateur
        $guide = $guidesRepository->findOneBy(['user' => $user]);

        // Vérifier si le guide existe
        if (!$guide) {
            $this->addFlash('error', 'Vous devez être un guide pour voir les avis.');
            return $this->redirectToRoute('app_user_account_index');
        }

        $reviews = $reviewsRepository->findBy([
            'guide' => $guide,
            'validate' => true,
        ]);

        return $this->render('guide/reviews/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }

    #[Route('/{id}/details', name: 'details')]
    public function detailsReviews(int $id, ReviewsRepository $reviewsRepository, GuidesRepository $guidesRepository): Response
    {
        // Récupérer le guide par son ID
        $reviewedGuide = $guidesRepository->find($id);

        // Vérifier si ce guide existe
        if (!$reviewedGuide) {
            $this->addFlash('error', 'Le guide que vous cherchez n\'existe pas.');
            return $this->redirectToRoute('app_reservations_index');
        }


        // Récupérer les avis validés associés au guide
        $reviews = $reviewsRepository->findBy([
            'guide' => $reviewedGuide,
            'validate' => true,
        ]);

        // Obtenir la note moyenne
        $averageRating = $reviewsRepository->getAverageRatingForGuide($id);

        // Obtenir le nombre total d'avis
        $totalReviews = $reviewsRepository->countReviews($id);

        // Obtenir la répartition des avis par note
        $ratingsDistribution = $reviewsRepository->countReviewsByRating($id);

        return $this->render('guide/reviews/details.html.twig', [
            'guide' => $reviewedGuide,
            'reviews' => $reviews,
            'averageRating' => $averageRating,
            'totalReviews' => $totalReviews,
            'ratingsDistribution' => $ratingsDistribution,
        ]);
    }
}
