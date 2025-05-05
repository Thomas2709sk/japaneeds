<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationsController extends AbstractController
{
    #[Route('/user/reservations', name: 'app_user_reservations')]
    public function index(): Response
    {
        return $this->render('user/reservations/index.html.twig', [
            'controller_name' => 'ReservationsController',
        ]);
    }

    // A modifier
    // Ajouter ici fonction depuis le Reservations Controller général Cancel , Confirm et Bad
}
