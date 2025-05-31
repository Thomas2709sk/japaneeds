<?php

namespace App\Controller\regions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

    #[Route('/regions/kansai', name: 'app_regions_kansai_')]
 class KansaiController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('regions/kansai/index.html.twig', [
        ]);
    }
}
