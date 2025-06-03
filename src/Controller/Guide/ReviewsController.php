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
        // get User
        $user = $this->getUser();

        // get the guide ID associate with the user ID
        $guide = $guidesRepository->findOneBy(['user' => $user]);

        // if guide exist
        if (!$guide) {
            $this->addFlash('error', 'Vous devez être un guide pour voir les avis.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // find reviews for each guide and only show if validate = true
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
        // get guide by its ID
        $reviewedGuide = $guidesRepository->find($id);

        // if guide exist
        if (!$reviewedGuide) {
            $this->addFlash('error', 'Le guide que vous cherchez n\'existe pas.');
            return $this->redirectToRoute('app_reservations_index');
        }


        // find reviews for each guide and only show if validate = true
        $reviews = $reviewsRepository->findBy([
            'guide' => $reviewedGuide,
            'validate' => true,
        ]);

        // get the average rating of the guide 
        $averageRating = $reviewsRepository->getAverageRatingForGuide($id);

        // get total reviews
        $totalReviews = $reviewsRepository->countReviews($id);

        // get total of reviews for each rate
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
