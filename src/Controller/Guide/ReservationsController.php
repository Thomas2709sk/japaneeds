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
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Vérifie si l'utilisateur est bien une instance de Users
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

        // Récupérer l'entité Guide associée à l'utilisateur connecté
        $guide = $em->getRepository(Guides::class)->findOneBy(['user' => $user]);

        // On récupère le numéro de page
        $page = $request->query->get('page', 1);


         // Récupérer les réservations paginées associées au guide
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
        // Récupérer l'utilisateur actuellement connecté
        $user = $this->getUser();

        // Créer un objet Reservation vide
        $reservations = new Reservations();
        $form = $this->createForm(AddReservationFormType::class, $reservations);

        // Récupérer le guide associé à l'utilisateur connecté
        $guide = $guidesRepository->findOneBy(['user' => $user]);

        // Vérifier si le guide existe
        if (!$guide) {
            $this->addFlash('error', 'Vous devez être un guide pour créer une réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Récupérer les villes associées au guide
        $selectedCities = $guide->getCities();

        // Passer les villes sélectionnées au formulaire
        $form = $this->createForm(AddReservationFormType::class, $reservations, [
            'selected_cities' => $selectedCities
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Associer le guide à la réservation
            $reservations->setGuide($guide);


            // Définir placesDispo à la valeur de nbplaces du guide
            $reservations->setPlacesDispo($guide->getNbplaces() ?? 0);

            // Enregistrer la réservation
            // Le token est automatiquement généré par le constructeur
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
        // Vérification de l'autorisation avec le Voter
        if (!$authChecker->isGranted(ReservationsVoter::DELETE, $reservation)) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer cette réservation.');
        }
    
        // Récupérer les utilisateurs liés à cette réservation
        $users = $reservation->getUsers(); 
   
        // Envoyer un email à chaque utilisateur
           // Rendre les crédits à l'utilisateur lors de l'annulation
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

                // Suppression de la réservation
                $em->remove($reservation);
                $em->flush();
    
        // Flash message et redirection
        $this->addFlash('success', 'La réservation a été supprimée et les utilisateurs ont été notifiés.');
    
        return $this->redirectToRoute('app_guide_account_index');
    }

    #[Route('/start/{id}', name: 'start', methods: ['POST'])]
    public function startReservation(Reservations $reservation, EntityManagerInterface $entityManager): Response
    {
        // Vérifie que l'utilisateur connecté est le guide de la réservation
        if ($this->getUser() !== $reservation->getGuide()->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas démarrer cette réservation.');
        }
    
        // Vérifie que la réservation est dans le bon statut
        if ($reservation->getStatus() !== 'A venir') {
            $this->addFlash('error', 'Cette réservation ne peut pas être démarrée.');
            return $this->redirectToRoute('app_guide_account_index');
        }

    
        // Met à jour le statut de la réservation
        $reservation->setStatus('En cours');
        $entityManager->persist($reservation);
        $entityManager->flush();
    
        $this->addFlash('success', 'La réservation a été démarrée avec succès.');
    
        return $this->redirectToRoute('app_guide_account_index');
    }

    #[Route('/end/{id}', name: 'end', methods: ['POST'])]
    public function endReservation(Reservations $reservation, EntityManagerInterface $entityManager,  SendEmailService $mail): Response
    {
        // Vérifie que l'utilisateur connecté est le guide de la réservation
        if ($this->getUser() !== $reservation->getGuide()->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas finir cette réservation.');
        }
    
        // Vérifie que la réservation est dans le bon statut
        if ($reservation->getStatus() !== 'En cours') {
            $this->addFlash('error', 'Cette réservation ne peut pas être terminée.');
            return $this->redirectToRoute('app_guide_account_index');
        }

         // Récupérer les utilisateurs liés à cette réservation
         $users = $reservation->getUsers(); 

         // Envoyer un email à chaque utilisateur
         foreach ($users as $user) {
            $mail->send(
                'no-reply@japan.test',
                $user->getEmail(),
                'Fin de votre réservation',
                'reservation_end',
                compact('user', 'reservation') // ['user'=> $user, 'reservation' => $reservation]
            );
        }
    
        // Met à jour le statut de la réservation
        $reservation->setStatus('Fini');
        $entityManager->persist($reservation);
        $entityManager->flush();
    
        $this->addFlash('success', 'La réservation a été terminée avec succès.');
    
        // Redirige vers la liste des réservations ou une autre page
        return $this->redirectToRoute('app_guide_account_index');
    }
}
