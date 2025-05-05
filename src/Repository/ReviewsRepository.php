<?php

namespace App\Repository;

use App\Entity\Reviews;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reviews>
 */
class ReviewsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reviews::class);
    }

    public function getAverageRatingForGuide(int $guideId): ?float
    {
        $qb = $this->createQueryBuilder('r')
            ->select('AVG(r.rate) as avgRate')
            ->where('r.guide = :guideId')
            ->setParameter('guideId', $guideId)
            ->andWhere('r.validate = true');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countReviews(int $guideId): int
{
    $qb = $this->createQueryBuilder('r')
        ->select('COUNT(r.id)')
        ->where('r.guide = :guideId')
        ->andWhere('r.validate = true') // Filtre uniquement les avis validés
        ->setParameter('guideId', $guideId);

    return (int) $qb->getQuery()->getSingleScalarResult();
}

public function countReviewsByRating(int $guideId): array
{
    $qb = $this->createQueryBuilder('r')
        ->select('r.rate, COUNT(r.id) as reviewCount')
        ->where('r.guide = :guideId')
        ->andWhere('r.validate = true')
        ->setParameter('guideId', $guideId)
        ->groupBy('r.rate');

    $result = $qb->getQuery()->getResult();

    // Préparer un tableau avec des clés de 1 à 5 pour s'assurer que toutes les notes sont couvertes
    $ratingsDistribution = array_fill(1, 5, 0);
    foreach ($result as $row) {
        $ratingsDistribution[$row['rate']] = (int) $row['reviewCount'];
    }

    return $ratingsDistribution;
}

    //    /**
    //     * @return Reviews[] Returns an array of Reviews objects
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

    //    public function findOneBySomeField($value): ?Reviews
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
