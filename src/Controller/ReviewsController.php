<?php

namespace App\Controller;

use App\Document\Review;
use App\Form\ReviewsWebsiteFormType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reviews', name: 'app_reviews_')]
class ReviewsController extends AbstractController
{
   #[Route('/', name: 'index')]
public function index(Request $request, DocumentManager $dm): Response
{
     $review = new Review();
     $review->setCreatedAt(new \DateTime());

    $reviewWebForm = $this->createForm(ReviewsWebsiteFormType::class, $review);
    $reviewWebForm->handleRequest($request);

    if ($reviewWebForm->isSubmitted() && $reviewWebForm->isValid()) {
        $dm->persist($review);
        $dm->flush();

        $this->addFlash('success', 'Votre avis a été envoyé avec succès.');
        return $this->redirectToRoute('app_reviews_index');
    }

    return $this->render('reviews/index.html.twig', [
        'reviewWebForm' => $reviewWebForm->createView(),
    ]);
}

}
