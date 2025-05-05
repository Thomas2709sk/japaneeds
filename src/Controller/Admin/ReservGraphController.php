<?php

// src/Controller/Admin/GraphController.php

namespace App\Controller\Admin;

use App\Repository\ReservationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/graph', name: 'app_admin_graph_')]
class ReservGraphController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/graph/index.html.twig');
    }

        #[Route('/reserv', name: 'graph', methods: ['GET'])]
        public function getReservationsData(
            ReservationsRepository $reservationRepository,
            Security $security // Vérifiez les permissions de l'utilisateur
        ): JsonResponse {
            // Vérifiez si l'utilisateur a les droits nécessaires
            if (!$security->isGranted('ROLE_ADMIN')) {
                return new JsonResponse(['error' => 'Access Denied'], 403);
            }
    
            // Définir la locale en français
            setlocale(LC_TIME, 'fr_FR.UTF-8');
    
            // Appel au repository pour récupérer les données
            $reservations = $reservationRepository->reservationsByDay();
            
            // Regrouper les réservations par mois et jour
            $aggregatedData = [];
            foreach ($reservations as $reservation) {
                if ($reservation['date'] instanceof \DateTime) {
                    $monthYear = $reservation['date']->format('Y-m'); // Exemple : "2025-03"
                    $day = $reservation['date']->format('d'); // Jour du mois (exemple : "31")
                    
                    // Initialisation si le mois n'existe pas
                    if (!isset($aggregatedData[$monthYear])) {
                        $aggregatedData[$monthYear] = [];
                    }
    
                    $aggregatedData[$monthYear][$day] = ($aggregatedData[$monthYear][$day] ?? 0) + $reservation['count'];
                }
            }
            
            // Préparer les données pour les renvoyer
            $data = [];
            foreach ($aggregatedData as $monthYear => $days) {
                $date = new \DateTime($monthYear . "-01"); // Crée une date pour déterminer le nombre de jours
                $daysInMonth = $date->format('t'); // Nombre de jours dans le mois
    
                // Utiliser IntlDateFormatter pour obtenir le mois en français
                $formatter = new \IntlDateFormatter(
                    'fr_FR',
                    \IntlDateFormatter::LONG,
                    \IntlDateFormatter::NONE,
                    null,
                    null,
                    'MMMM yyyy'
                );
                $monthInFrench = $formatter->format($date);
                
                // Créer les labels et counts
                $labels = [];
                $counts = [];
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $day = str_pad($i, 2, '0', STR_PAD_LEFT); // Ajout du zéro pour les jours < 10
                    $labels[] = $day;
                    $counts[] = $days[$day] ?? 0; // Valeur par défaut : 0
                }
    
                $data[] = [
                    'month' => $monthInFrench, // Mois et année en français (exemple : "mars 2025")
                    'labels' => $labels,
                    'counts' => $counts,
                ];
            }
            
            return new JsonResponse($data);
        }
    }

