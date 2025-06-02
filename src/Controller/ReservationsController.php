<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Reservations;
use App\Form\SearchFiltersFormType;
use App\Form\SearchReservationsFormType;
use App\Repository\ReservationsRepository;
use App\Repository\ReviewsRepository;
use App\Repository\UsersRepository;
use App\Service\SendEmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/reservations', name: 'app_reservations_')]
class ReservationsController extends AbstractController
{

    #[Route('/', name: 'index')]
    public function index(
        Request $request,
        UsersRepository $usersRepository
    ): Response {
        
         // Créer le formulaire
        $form = $this->createForm(SearchReservationsFormType::class);

        // Traiter la requête
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData(); // Récupérer les données du formulaire

            // Redirect to results page with the day and date in the URL from the method 'GET'
            return $this->redirectToRoute('app_reservations_results', [
                'day' => $data['date'] ? $data['date']->format('Y-m-d') : null,
                'city' => $data['city'],
            ]);
        }

        // Find 4 guides in the guides table
        $users = $usersRepository->findBy([], null, 4);

        return $this->render(
            'reservations/index.html.twig',
            [
                'form' => $form->createView(),
                'users' => $users,
            ]
        );
    }


    #[Route(path: '/details/{id}', name: 'details')]
    public function details($id, ReservationsRepository $reservationsRepository, ReviewsRepository $reviewsRepository): Response
    {
        // search the reservation by its ID
        $reservation = $reservationsRepository->findOneBy(['id' => $id]);

        // if reservaton don't exist
        if (!$reservation) {
            throw $this->createNotFoundException('Cette réservation n\'existe pas');
        }

        // search the guide associate with the reservation
        $guide = $reservation->getGuide();

        // Use 'getAverageRatingForGuide' in the Repository to calculate the Average rating of each guide
        $averageRating = $reviewsRepository->getAverageRatingForGuide($guide->getId());

        // Use 'countReviews' in the Repository to have the number of reviews for each guide
        $reviewCount = $reviewsRepository->countReviews($guide->getId());

        // $averageRating = round($averageRating, 2);

        // if reservation exist
        return $this->render('reservations/details.html.twig', compact('reservation', 'averageRating', 'reviewCount'));
    }

    // Book Réservations (PlacesDispo a modifier avec nbPlaces "+-1 pour l'instant")
    #[Route('/book/{id}', name: 'book')]
    public function book(Reservations $reservation, EntityManagerInterface $em, SendEmailService $mail)
    {   
        // look if user is logged and have 'ROLE_USER'
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        // get User
        $user = $this->getUser();

        // if User is type of Users
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }

    // If User book reservation
        // Déduct the user credits depending of reservations price
        $user->setCredits($user->getCredits() - $reservation->getPrice());

        // Add the user to the reservation
        $reservation->addUser($user);

        // Add the reservation to the user account
        $user->addReservation($reservation);


        // edit places available of the reservation
        $reservation->setPlacesDispo($reservation->getPlacesDispo() - 1);

        // persist on the DB
        $em->persist($reservation);
        $em->persist($user);
        $em->flush();

        // Send mail to the user
        $mail->send(
            'no-reply@japan.test',
            $user->getEmail(),
            'Confirmation de votre réservation',
            'reservation_confirm',
            compact('user', 'reservation') // ['user'=> $user]
        );

        $this->addFlash('success', 'Votre réservation a été effectuée avec succès !');

        return $this->redirectToRoute('app_user_account_index');
    }

    // Annuler Réservations (PlacesDispo a modifier avec nbPlaces "+-1 pour l'instant")
    #[Route('cancel/{reservationId}', name: 'cancel')]
    public function cancel(int $reservationId, EntityManagerInterface $em): Response
    {
        // get the User
        $user = $this->getUser();

        // look if user is logged
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not authenticated.');
        }

        // search the reservation with its ID
        $reservation = $em->getRepository(Reservations::class)->find($reservationId);

        // if reservation don't exist
        if (!$reservation) {
            $this->addFlash('error', 'La réservation n\'existe pas.');
            return $this->redirectToRoute('app_reservations_index');
        }

        // if the user made the reservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez annuler cette réservation.');
            return $this->redirectToRoute('app_reservations_index');
        }

        // if user cancel get credits back
        $user->setCredits($user->getCredits() + $reservation->getPrice());

        // remove user from reservation
        $reservation->removeUser($user);

        // edit number of places available
        $reservation->setPlacesDispo($reservation->getPlacesDispo() + 1);

        // Save in DB
        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Votre réservation a été annulée avec succès.');

        return $this->redirectToRoute('app_user_account_index');
    }

    // if user say the reservation was good
    #[Route('/confirm/{id}', name: 'confirm')]
    public function confirm(
        int $id,
        Reservations $reservation,
        EntityManagerInterface $em
    ) {
        // get the User
        $user = $this->getUser();

         // search the reservation with its ID
        $reservation = $em->getRepository(Reservations::class)->find($id);

        // if User is part of the reservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Réservation invalide ou non autorisée.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // if user can confirm the end of the reservation
        if ($reservation->getStatus() !== 'Fini') {
            $this->addFlash('error', 'Cette réservation ne peut pas être confirmée.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Count credits for guide and admin
        $price = $reservation->getPrice();
        $platformFee = 2;
        $creditsForGuide = $price - $platformFee;

        $guide = $reservation->getGuide();

        // find admin by its id and if 'ROLE_ADMIN'
        $admin = $em->getRepository(Users::class)->find(1);

        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles(), true)) {
            throw new \Exception('L\'utilisateur avec l\'ID 1 n\'est pas un administrateur.');
        }

        // get the guide by its user id
        $userGuide = $guide->getUser();

        // Send credits
             // Add to guide by its user ID
        $userGuide->setCredits($userGuide->getCredits() + $creditsForGuide);
            // Add to admin
        if ($admin) {
            $admin->setCredits($admin->getCredits() + $platformFee); 
        }

        // edit reservation status to 'Confirmé'
        $reservation->setStatus('Confirmé');

        // Save to DB
        $em->persist($guide);
        $em->persist($reservation);
        if ($admin) {
            $em->persist($admin);
        }

        $em->flush();


        $this->addFlash('success', 'Merci de votre confirmation, vous pouvez laisser un avis à votre guide ! !');
        return $this->redirectToRoute('app_user_account_index');
    }

    // If user say the reservation was bad
    #[Route('/bad/{id}', name: 'bad', methods: ['POST'])]
    public function createBadReviews(
        int $id,
        EntityManagerInterface $em
    ): Response {
        // get User
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        // find reservation
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation) {
            $this->addFlash('error', 'La réservation est introuvable.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // search if user is part of the reservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // edit status of reservation
        $reservation->setStatus('Vérification par la plateforme');
        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Veuillez laisser un avis.');

        return $this->redirectToRoute('app_user_reviews_bad', [
            'reservationId' => $id,
        ]);
    }

    #[Route('/search', name: 'search')]
    public function search(Request $request): Response
    {
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

        return $this->render('reservations/search.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/results', name: 'results')]
    public function results(
        Request $request,
        ReservationsRepository $reservationsRepository
    ): Response {
        // get the day and city in the url of the search form
        $day = $request->query->get('day');
        $city = $request->query->get('city');
    
    
        // add the day and city of the search form in the filters form
        $filtersForm = $this->createForm(SearchFiltersFormType::class, [
            'date' => $day ? new \DateTime($day) : null,
            'city' => $city,
        ]);
    
        $filtersForm->handleRequest($request);
    
        $filters = [
            'date' => $day ? new \DateTime($day) : null,
            'city' => $city,
        ];
    
        if ($filtersForm->isSubmitted() && $filtersForm->isValid()) {
            // Add filters used by user
            $filters = array_merge($filters, $filtersForm->getData());
        }
    
        // Update reservation with filters used by user
        $reservations = $reservationsRepository->findReservationsWithGuideRatings(
            $filters['date'] ? $filters['date']->format('Y-m-d') : null,
            $filters['city'],
            $filters
        );
    
        // if no reservation on choosen day use 'findClosestDay' to search for the next available day 
        $findClosestDay = null;
        if (empty($reservations) && $day && $city) {
            $findClosestDay = $reservationsRepository->findClosestDay($day, $city);
        }
    
        return $this->render('reservations/results.html.twig', [
            'filtersForm' => $filtersForm->createView(),
            'reservations' => $reservations,
            'findClosestDay' => $findClosestDay,
            'city' => $city,
        ]);
    }
}
