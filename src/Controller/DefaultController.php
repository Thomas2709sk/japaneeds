<?php

namespace App\Controller;

use App\Document\Review;
use App\Form\SearchReservationsFormType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(DocumentManager $dm, Request $request)
    {

         // find all review from MongoDB
        $reviews = $dm->getRepository(Review::class)->findAll();

         // Create search Reservation form
        $form = $this->createForm(SearchReservationsFormType::class);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // return results page with the day and city of the search Reservation form 
            return $this->redirectToRoute('app_reservations_results', [
                'day' => $data['date'] ? $data['date']->format('Y-m-d') : null,
                'city' => $data['city'],
            ]);
        }

        return $this->render('default/index.html.twig', [
            'reviews' => $reviews,
            'form' => $form->createView(),
        ]);
    }
}

