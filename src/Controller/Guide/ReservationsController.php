<?php

namespace App\Controller\Guide;

use App\Entity\Guides;
use App\Entity\Reservations;
use App\Entity\Users;
use App\Form\AddReservationFormType;
use App\Repository\GuidesRepository;
use App\Repository\CitiesRepository;
use App\Repository\ReservationsRepository;
use App\Security\Voter\ReservationsVoter;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route('/guide/reservations', name: 'app_guide_reservations_')]
class ReservationsController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(Request $request, EntityManagerInterface $em, ReservationsRepository $reservationsRepository): Response
    {
        // get User
        $user = $this->getUser();


        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

        // get the guide ID associate with the user ID
        $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);

        // get the number of pages ( start at 1)
        $page = $request->query->get('page', 1);


         // get all the reservations of the guide paginated
    $pagination = $reservationsRepository->getAllPaginated($guide, $page);

    // Retourner la vue avec les données paginées
    return $this->render('guide/reservations/index.html.twig', [
        'reservations' => $pagination['reservations'],
        'current_page' => $pagination['current'],
        'total_pages' => $pagination['pages'],
    ]);
    }

    #[Route('/add', name: 'add')]
    public function addReservation(Request $request, EntityManagerInterface $em, GuidesRepository $guidesRepository, CitiesRepository $citiesRepository): Response
    {
        // get User
        $user = $this->getUser();

        // Create new reservation object
        $reservations = new Reservations();

        // Create form
        $form = $this->createForm(AddReservationFormType::class, $reservations);

        // get the guide ID associate with the user ID
        $guide = $guidesRepository->findOneBy(['user' => $user]);

        // if guide don't exist
        if (!$guide) {
            $this->addFlash('error', 'Vous devez être un guide pour créer une réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // get the cities the guide selected in this account
        $selectedCities = $guide->getCities();

        // add the cities to the form
        $form = $this->createForm(AddReservationFormType::class, $reservations, [
            'selected_cities' => $selectedCities
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associate the guide to the reservation
            $reservations->setGuide($guide);


            // set 'PlacesDispo' for the reservation depending of 'NbPlaces' the guide set in this account
            $reservations->setPlacesDispo($guide->getNbplaces() ?? 0);

            // Add reservation
            // generate random token as uniqID for the reservation number to avoid having ID that follow
            $reservations->setReservNumber('RES#' . substr(bin2hex(random_bytes(4)), 0, 8));
            $em->persist($reservations);
            $em->flush();

            $this->addFlash('success', 'La réservation a été ajoutée.');
            return $this->redirectToRoute('app_guide_reservations_add');
        }

        return $this->render('guide/reservations/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function deleteReservation(
        Reservations $reservation,
        AuthorizationCheckerInterface $authChecker,
        EntityManagerInterface $em,
        SendEmailService $mail
    ): Response {
        // Check if guide have access to delete with the Voter
        if (!$authChecker->isGranted(ReservationsVoter::DELETE, $reservation)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette réservation.');
        }
    
        // Get all the Users from the reservations
        $users = $reservation->getUsers(); 
   
        // send mail to each users
           // set credits back to each users
        foreach ($users as $user) {
            $user->setCredits($user->getCredits() + $reservation->getPrice());
            $mail->send(
                'no-reply@japan.test',
                $user->getEmail(),
                'Annulation de votre réservation',
                'reservation_cancel',
                compact('user', 'reservation') // ['user'=> $user, 'reservation' => $reservation]
            );
        }

                // delete reservation
                $em->remove($reservation);
                $em->flush();
    
        $this->addFlash('success', 'La réservation a été supprimée et les utilisateurs ont été notifiés.');
    
        return $this->redirectToRoute('app_guide_account_index');
    }

    // Guide click on start button on the reservation
    #[Route('/start/{id}', name: 'start', methods: ['POST'])]
    public function startReservation(Reservations $reservation, EntityManagerInterface $entityManager): Response
    {
        // check if the guide is the owner of the reservation
        if ($this->getUser() !== $reservation->getGuide()->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas démarrer cette réservation.');
        }
    
        // Check status reservation 
        if ($reservation->getStatus() !== 'A venir') {
            $this->addFlash('error', 'Cette réservation ne peut pas être démarrée.');
            return $this->redirectToRoute('app_guide_account_index');
        }

    
        // Update status reservation when guide start reservation
        $reservation->setStatus('En cours');
        $entityManager->persist($reservation);
        $entityManager->flush();
    
        $this->addFlash('success', 'La réservation a été démarrée avec succès.');
    
        return $this->redirectToRoute('app_guide_account_index');
    }

    // Guide click on end button of the reservation
    #[Route('/end/{id}', name: 'end', methods: ['POST'])]
    public function endReservation(Reservations $reservation, EntityManagerInterface $entityManager,  SendEmailService $mail): Response
    {
        // check if the guide is the owner of the reservation
        if ($this->getUser() !== $reservation->getGuide()->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas finir cette réservation.');
        }
    
        // Check status reservation 
        if ($reservation->getStatus() !== 'En cours') {
            $this->addFlash('error', 'Cette réservation ne peut pas être terminée.');
            return $this->redirectToRoute('app_guide_account_index');
        }

         // Get all the Users from the reservations
         $users = $reservation->getUsers(); 

         // send mail to each users
         foreach ($users as $user) {
            $mail->send(
                'no-reply@japan.test',
                $user->getEmail(),
                'Fin de votre réservation',
                'reservation_end',
                compact('user', 'reservation') // ['user'=> $user, 'reservation' => $reservation]
            );
        }
    
        // Update status
        $reservation->setStatus('Fini');
        $entityManager->persist($reservation);
        $entityManager->flush();
    
        $this->addFlash('success', 'La réservation a été terminée avec succès.');
    
        return $this->redirectToRoute('app_guide_account_index');
    }
}
