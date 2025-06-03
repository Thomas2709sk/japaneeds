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
        // get User
        $user = $this->getUser();

 
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

         // get the reservation of the User
         $reservations = $user->getReservations();


        return $this->render('user/reservations/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    // A modifier
    // Ajouter ici fonction depuis le Reservations Controller général Cancel , Confirm et Bad
}
