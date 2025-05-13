<?php

namespace App\Controller;

use App\Document\Review;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(DocumentManager $dm)
    {

         // Récupérer les avis depuis MongoDB
        $reviews = $dm->getRepository(Review::class)->findAll();

        return $this->render('default/index.html.twig', [
            'reviews' => $reviews,
        ]);
    }
}

