<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Repository\ReservationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/credits/graph', name: 'app_admin_credits_graph_')]
class CreditsGraphController extends AbstractController
{
    
#[Route('/', name: 'index')]
public function index(Security $security): Response
{
    $admin = $security->getUser();

    // Check if User have 'ROLE_ADMIN'
    if ($admin instanceof Users && in_array('ROLE_ADMIN', $admin->getRoles())) {
        $credits = $admin->getCredits();
    } else {
        $credits = 0;
    }

    return $this->render('admin/credits_graph/index.html.twig', [
        'credits_total' => $credits,
    ]);
}

    #[Route('/credits', name: 'credits', methods: ['GET'])]
    public function getCreditsData(
        ReservationsRepository $reservationRepository,
        Security $security
    ): JsonResponse {
        // Check if User have 'ROLE_ADMIN'
        if (!$security->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Access Denied'], 403);
        }

        // Use creditsByDay in the reservation Repository to count credits
        $credits = $reservationRepository->creditsByDay();

        // get the credits by day and month
        $aggregatedData = $this->aggregateCreditsByMonth($credits);

        // get the formated data for the front
        $data = $this->formatAggregatedData($aggregatedData);

        return new JsonResponse($data);
    }

    /**
     * Agrège les crédits par mois et jour.
     */
    private function aggregateCreditsByMonth(array $credits): array
    {
        $aggregatedData = [];
        foreach ($credits as $credit) {
            if (!isset($credit['date']) || !$credit['date'] instanceof \DateTime) {
                continue;
            }

            $monthYear = $credit['date']->format('Y-m');
            $day = $credit['date']->format('d'); 

            // Initialisation si le mois n'existe pas encore
            if (!isset($aggregatedData[$monthYear])) {
                $aggregatedData[$monthYear] = [];
            }

            $aggregatedData[$monthYear][$day] = ($aggregatedData[$monthYear][$day] ?? 0) + $credit['count'];
        }

        return $aggregatedData;
    }

    /**
     * Formate les données agrégées pour le frontend.
     */
    private function formatAggregatedData(array $aggregatedData): array
    {
        $data = [];
        foreach ($aggregatedData as $monthYear => $days) {
            // Vérifier s'il y a au moins un jour avec des crédits pour ce mois
            if (array_sum($days) === 0) {
                continue;
            }

            $date = new \DateTime($monthYear . '-01'); // Crée une date pour déterminer les jours du mois
            $daysInMonth = $date->format('t');

            // Utiliser IntlDateFormatter pour obtenir le mois en français
            $monthFrench = $this->formatMonthInFrench($date);

            // Créer les labels et counts pour chaque jour du mois
            [$labels, $counts] = $this->createLabelsAndCounts($days, $daysInMonth);

            $data[] = [
                'month' => $monthFrench,
                'labels' => $labels,
                'counts' => $counts,
            ];
        }

        return $data;
    }

    /**
     * Formate un mois en français
     */
    private function formatMonthInFrench(\DateTime $date): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::LONG,
            \IntlDateFormatter::NONE,
            null,
            null,
            'MMMM yyyy'
        );

        return $formatter->format($date);
    }

    /**
     * Crée les labels (jours) et les counts (crédits) pour un mois donné.
     */
    private function createLabelsAndCounts(array $days, int $daysInMonth): array
    {
        $labels = [];
        $counts = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $day = str_pad($i, 2, '0', STR_PAD_LEFT);
            $labels[] = $day;
            $counts[] = $days[$day] ?? 0;
        }

        return [$labels, $counts];
    }
}
