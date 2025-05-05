<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Reservations;
use App\Entity\Reviews;
use App\Form\ReviewsGuideFormType;
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
        ReservationsRepository $reservationsRepository,
        UsersRepository $usersRepository
    ): Response {

        // Récupérez les utilisateurs (guides)
        $users = $usersRepository->findBy([], null, 3);

        // Récupérez toutes les réservations
        $reservationsWithRatings = $reservationsRepository->findAllWithAverageRatings();

        return $this->render(
            'reservations/index.html.twig',
            [
                'reservationsWithRatings' => $reservationsWithRatings,
                'users' => $users,
            ]
        );
    }

    #[Route(path: '/details/{id}', name: 'details')]
    public function details($id, ReservationsRepository $reservationsRepository, ReviewsRepository $reviewsRepository): Response
    {
        $reservation = $reservationsRepository->findOneBy(['id' => $id]);

        // Si la réservation n'existe pas
        if (!$reservation) {
            throw $this->createNotFoundException('Cette réservation n\'existe pas');
        }

        // Récupère le guide associé à la réservation
        $guide = $reservation->getGuide();

        // Calcul de la moyenne des notes pour ce guide
        $averageRating = $reviewsRepository->getAverageRatingForGuide($guide->getId());

        // Récupère le nombre d'avis pour ce guide
        $reviewCount = $reviewsRepository->countReviews($guide->getId());

        // $averageRating = round($averageRating, 2);

        // La réservation existe
        return $this->render('reservations/details.html.twig', compact('reservation', 'averageRating', 'reviewCount'));
    }

    // Book Réservations (PlacesDispo a modifier avec nbPlaces "+-1 pour l'instant")

    #[Route('/book/{id}', name: 'book')]
    public function book(Reservations $reservation, EntityManagerInterface $em, SendEmailService $mail)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        // Vérifiez si l'utilisateur est bien une instance de Users
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not of type Users.');
        }


        // Déduire les crédits de l'utilisateur
        $user->setCredits($user->getCredits() - $reservation->getPrice());

        // Ajouter l'utilisateur à la réservation
        $reservation->addUser($user);

        // Ajouter la réservation à l'utilisateur
        $user->addReservation($reservation);


        // Mettre à jour les places disponibles
        $reservation->setPlacesDispo($reservation->getPlacesDispo() - 1);

        // Sauvegarder dans la base de données
        $em->persist($reservation);
        $em->persist($user);
        $em->flush();

        // Envoyer l'email
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
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user instanceof Users) {
            throw new \LogicException('The user is not authenticated.');
        }

        // Trouver la réservation
        $reservation = $em->getRepository(Reservations::class)->find($reservationId);

        // Vérifier si la réservation existe
        if (!$reservation) {
            $this->addFlash('error', 'La réservation n\'existe pas.');
            return $this->redirectToRoute('app_reservations_index');
        }

        // Vérifier si l'utilisateur est celui qui a fait la réservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez annuler cette réservation.');
            return $this->redirectToRoute('app_reservations_index');
        }

        // Rendre les crédits à l'utilisateur lors de l'annulation
        $user->setCredits($user->getCredits() + $reservation->getPrice());

        // Retirer l'utilisateur de la réservation
        $reservation->removeUser($user);

        // Réincrémenter le nombre de places disponibles
        $reservation->setPlacesDispo($reservation->getPlacesDispo() + 1);

        // Sauvegarder dans la base de données
        $em->persist($reservation);
        $em->flush();

        $this->addFlash('success', 'Votre réservation a été annulée avec succès.');

        return $this->redirectToRoute('app_user_account_index');
    }

    #[Route('/confirm/{id}', name: 'confirm')]
    public function confirm(
        int $id,
        Reservations $reservation,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();
        $reservation = $em->getRepository(Reservations::class)->find($id);

        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Réservation invalide ou non autorisée.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Vérifier que la réservation peut être confirmée
        if ($reservation->getStatus() !== 'Fini') {
            $this->addFlash('error', 'Cette réservation ne peut pas être confirmée.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Calculer les crédits pour le guide et l'admin
        $price = $reservation->getPrice();
        $platformFee = 2;
        $creditsForGuide = $price - $platformFee;

        $guide = $reservation->getGuide();

        // Rechercher l'admin
        $admin = $em->getRepository(Users::class)->find(1);

        if (!$admin || !in_array('ROLE_ADMIN', $admin->getRoles(), true)) {
            throw new \Exception('L\'utilisateur avec l\'ID 1 n\'est pas un administrateur.');
        }

        // Récupérer l'utilisateur associé au Guide
        $userGuide = $guide->getUser();

        // Transférer les crédits
        $userGuide->setCredits($userGuide->getCredits() + $creditsForGuide); // Ajouter au guide (via l'utilisateur associé)
        if ($admin) {
            $admin->setCredits($admin->getCredits() + $platformFee); // Ajouter la commission à l'admin
        }

        // Mettre à jour le statut de la réservation
        $reservation->setStatus('Confirmé');

        // Sauvegarder les modifications
        $em->persist($guide);
        $em->persist($reservation);
        if ($admin) {
            $em->persist($admin);
        }

        $em->flush();


        $this->addFlash('success', 'Merci de votre confirmation, vous pouvez laisser un avis à votre guide ! !');
        return $this->redirectToRoute('app_user_account_index');
    }

    #[Route('/bad/{id}', name: 'bad', methods: ['POST'])]
    public function createBadReviews(
        int $id,
        EntityManagerInterface $em
    ): Response {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer cette action.');
        }

        // Trouver la réservation
        $reservation = $em->getRepository(Reservations::class)->find($id);
        if (!$reservation) {
            $this->addFlash('error', 'La réservation est introuvable.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Vérifier si l'utilisateur a participé à cette réservation
        if (!$reservation->getUsers()->contains($user)) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette réservation.');
            return $this->redirectToRoute('app_user_account_index');
        }

        // Mettre à jour le statut de la réservation
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

        // Récupérer les paramètres de l'URL (jour et ville)
        $day = $request->query->get('day');
        $city = $request->query->get('city');

        // Récupérer les réservations avec les notes moyennes des guides (par jour et ville)
        $reservations = $reservationsRepository->findReservationsWithGuideRatings($day, $city);

        // Appliquer les filtres supplémentaires si le formulaire est soumis
        $filtersForm = $this->createForm(SearchFiltersFormType::class);
        $filtersForm->handleRequest($request);

        if ($filtersForm->isSubmitted() && $filtersForm->isValid()) {
            $filters = $filtersForm->getData();
            $reservations = $reservationsRepository->filterReservations($reservations, $filters);
        }

        return $this->render('reservations/results.html.twig', [
            'filtersForm' => $filtersForm->createView(),
            'reservations' => $reservations,
        ]);
    }
}
