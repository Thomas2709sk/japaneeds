<?php

namespace App\Repository;

use App\Entity\Guides;
use App\Entity\Reservations;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    // Count reservation per day for admin chart
    public function reservationsByDay(): array
    {

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT r.day AS date, COUNT(r.id) AS count
             FROM App\Entity\Reservations r
             GROUP BY r.day
             ORDER BY r.day ASC'
        );

        return $query->getResult();   
    }

    //Count 2 credits for the admin when reservation status is 'Confirmé' and count by day for credits chart for admin
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

//  Recherche jour proche (avec du php)
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

// Show all informations  needed for guides and reservations
/**
* - price (float) : filtre sur le prix de la résa sur un mini (float) maxi (float)
*/
public function findReservationsWithGuideRatings(?string $day, ?string $city, ?array $filters = null): array
{
$qb = $this->createQueryBuilder('r')
->select('r', 'AVG(rev.rate) as averageRating')
->join('r.guide', 'g')
->leftJoin('g.reviews', 'rev', 'WITH', 'rev.validate = true') // Show reviews only if validated by admin
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


// Filtrer par prix
if ($price = ($filters['price'] ?? null)) {
$qb->andWhere('r.price <= :price')
->setParameter('price', $price);
}

// Filtrer par repas inclus
if (array_key_exists('meal', $filters) && $filters['meal'] !== null) {
    $qb->andWhere('r.meal = :meal')
       ->setParameter('meal', $filters['meal']);
}

// Filtrer par note moyenne minimale
if ($rate = ($filters['rate'] ?? null)) {
    $qb->andHaving('averageRating >= :rate') // Utiliser "andHaving" pour les agrégations
       ->setParameter('rate', $rate);
}

// Filtrer par heure de début
if ($begin = ($filters['begin'] ?? null)) {
$qb->andWhere('r.begin <= :begin')
->setParameter('begin', $begin);
}

return $qb->getQuery()->getResult();
}

// Paginate reservations for each guide
public function getAllPaginated(Guides $guide, int $page = 1, int $limit = 4): array
{
    $offset = ($page - 1) * $limit;

    $query = $this->createQueryBuilder('r')
        ->select('DISTINCT r') // Éviter les doublons
        ->where('r.guide = :guide')
        ->setParameter('guide', $guide)
        ->orderBy('r.id', 'DESC')
        ->setFirstResult($offset)
        ->setMaxResults($limit);

    $paginator = new Paginator($query, true); // Active le mode fetchJoin pour gérer les relations

    $data = $paginator->getQuery()->getResult();

    return [
        'reservations' => $data,
        'pages' => ceil($paginator->count() / $limit), // Nombre total de pages
        'current' => $page, // Page actuelle
    ];
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
