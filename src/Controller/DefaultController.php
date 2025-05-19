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

         // Récupérer les avis depuis MongoDB
        $reviews = $dm->getRepository(Review::class)->findAll();

         // Créer le formulaire
        $form = $this->createForm(SearchReservationsFormType::class);

        // Traiter la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // Récupérer les données du formulaire

            // Rediriger vers la page des résultats avec les paramètres dans l'URL
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

