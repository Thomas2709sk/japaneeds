<?php

namespace App\Controller\User;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user/reservations', name: 'app_user_reservations_')]
class ReservationsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifiez si l'utilisateur est bien une instance de Users
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

         // Récupérer les réservations de l'utilisateur
         $reservations = $user->getReservations();


        return $this->render('user/reservations/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    // A modifier
    // Ajouter ici fonction depuis le Reservations Controller général Cancel , Confirm et Bad
}
