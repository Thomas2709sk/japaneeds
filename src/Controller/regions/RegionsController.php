<?php

namespace App\Controller\regions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

    #[Route('/regions', name: 'app_regions_')]
class RegionsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('regions/regions/index.html.twig', [

        ]);
    }
}
