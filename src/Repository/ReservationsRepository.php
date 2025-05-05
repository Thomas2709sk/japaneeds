<?php

namespace App\Repository;

use App\Entity\Reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservations>
 */
class ReservationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservations::class);
    }

    public function reservationsByDay(): array
    {
        // Requête DQL pour compter les réservations par jour
        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT r.day AS date, COUNT(r.id) AS count
             FROM App\Entity\Reservations r
             GROUP BY r.day
             ORDER BY r.day ASC'
        );

        return $query->getResult();   
    }
    //Nombre de Credits pour chaque reservations a ajouter au graphique de credits admin
    // A modifier ( cause status)
    public function creditsByDay(): array
{
    $entityManager = $this->getEntityManager();

    $query = $entityManager->createQuery(
        'SELECT r.day AS date, SUM(2) AS count
         FROM App\Entity\Reservations r
         WHERE r.status = :status
         GROUP BY r.day
         ORDER BY r.day ASC'
    )->setParameter('status', 'Confirmé');

    return $query->getResult();
}

// Recherche toutes les reservations et calcule la note moyenne de chaque guide
// A Supprimer ??
public function findAllWithAverageRatings(): array
{
    return $this->createQueryBuilder('r')
        ->select('r as reservation', 'AVG(rv.rate) as averageRating')
        ->leftJoin('r.guide', 'g')
        ->leftJoin('g.reviews', 'rv', 'WITH', 'rv.validate = true') // Jointure avec condition sur les avis validés
        ->groupBy('r.id')
        ->getQuery()
        ->getResult();
}

// Recherche par ville et date
// A supprimer ??
public function findByFilters(?string $date, ?string $city): array
{
    $qb = $this->createQueryBuilder('r');

    // Ajouter un filtre sur la date si elle est spécifiée
    if ($date) {
        $qb->andWhere('r.day = :day')
           ->setParameter('day', $date);
    }

    // Ajouter un filtre sur la ville si elle est spécifiée
    if ($city) {
        $qb->join('r.city', 'c') // Joindre l'entité Cities
           ->andWhere('c.name LIKE :city') // Filtrer sur le champ name de Cities
           ->setParameter('city', '%' . $city . '%');
    }

    // Trier par date (optionnel)
    $qb->orderBy('r.day', 'ASC');

    return $qb->getQuery()->getResult();
}

//Recherche par ville et date - JOIN guides et reviews
public function findReservationsWithGuideRatings(?string $day, ?string $city): array
{
    $qb = $this->createQueryBuilder('r')
        ->select('r', 'AVG(rev.rate) as averageRating')
        ->join('r.guide', 'g')
        ->leftJoin('g.reviews', 'rev', 'WITH', 'rev.validate = true')
        ->groupBy('r.id')
        ->orderBy('r.day', 'ASC');

    // Ajouter le filtre sur le jour
    if ($day) {
        $formattedDay = (new \DateTime($day))->format('Y-m-d');
        $qb->andWhere('r.day = :day')
            ->setParameter('day', $formattedDay);
    }

    // Ajouter le filtre sur la ville
    if ($city) {
        $qb->join('r.city', 'c')
            ->andWhere('LOWER(c.name) LIKE :city')
            ->setParameter('city', '%' . strtolower($city) . '%');
    }

    return $qb->getQuery()->getResult();
}

//Recherche reservations la plus proche superieur
public function findNextAvailableDate(string $day): ?\DateTime
{
    $result = $this->createQueryBuilder('r')
        ->where('r.day > :day')
        ->setParameter('day', $day)
        ->orderBy('r.day', 'ASC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    return $result ? $result->getDay() : null;
}


public function findClosestDay(\DateTime|string $day, string $cityName): ?\DateTime
{
    // Normaliser le paramètre $day en un objet DateTime
    $targetDate = $day instanceof \DateTime ? $day : new \DateTime($day);
    $now = new \DateTime(); // La date actuelle

    // Construire la requête pour récupérer toutes les dates futures ou égales à aujourd'hui pour une ville donnée
    $results = $this->createQueryBuilder('r')
        ->select('r.day')
        ->join('r.city', 'c') // Joindre la table Cities
        ->where('r.day >= :now') // Exclure les dates passées
        ->andWhere('c.name = :cityName') // Filtrer par le nom de la ville
        ->setParameter('now', $now->format('Y-m-d'))
        ->setParameter('cityName', $cityName)
        ->getQuery()
        ->getResult();

    if (empty($results)) {
        return null; // Aucune réservation disponible
    }

    $closestDate = null;
    $minDifference = PHP_INT_MAX; // Une grande valeur pour commencer la comparaison

    // Parcourir toutes les dates disponibles pour trouver la plus proche de la date cible
    foreach ($results as $result) {
        $currentDate = $result['day']; // Doctrine retourne déjà un objet DateTime
        $difference = abs($currentDate->getTimestamp() - $targetDate->getTimestamp());

        // Trouver la date avec la plus petite différence
        if ($difference < $minDifference) {
            $minDifference = $difference;
            $closestDate = $currentDate;
        }
    }

    return $closestDate;
}

public function filterReservations(array $reservations, array $filters): array
{
    return array_filter($reservations, function ($reservationData) use ($filters) {
        $reservation = $reservationData[0];
        $averageRating = $reservationData['averageRating']; // La note moyenne du guide

        // Filtrer par prix maximum
        if (!is_null($filters['price']) && $reservation->getPrice() > $filters['price']) {
            return false;
        }

        // Filtrer par repas inclus
        if (!is_null($filters['meal']) && $reservation->isMeal() !== $filters['meal']) {
            return false;
        }

        // Filtrer par note moyenne minimale
        if (!is_null($filters['rate']) && $averageRating < $filters['rate']) {
            return false;
        }

        // Filtrer par heure de début
        if (!is_null($filters['begin']) && $reservation->getBegin() < $filters['begin']) {
            return false;
        }

        return true;
    });
}


// public function findClosestDate(string $day): ?\DateTime
// {
//     $qb = $this->createQueryBuilder('r')
//         ->select('r.day')
//         ->addSelect('(ABS(r.day - :day)) AS HIDDEN distance')
//         ->setParameter('day', new \DateTime($day))
//         ->orderBy('distance', 'ASC')
//         ->setMaxResults(1);

//     $result = $qb->getQuery()->getOneOrNullResult();

//     return $result ? new \DateTime($result['day']) : null;
// }

    

    //    /**
    //     * @return Reservations[] Returns an array of Reservations objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Reservations
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
