
{% if app.user.guide is not empty %}
<li><a href="">tesf</a></li>
{% endif %}

<a href="https://www.google.com/maps/search/?api=1&query={{ reservation.pointDeRencontre }}" target="_blank">
    Voir sur Google Maps
</a>>



//DQL qui ne reconnais pas le datediff
public function findClosestAvailableDate(string $day): ?\DateTime
{
    $targetDate = new \DateTime($day);
    $now = new \DateTime();

    $result = $this->createQueryBuilder('r')
        ->select('r.day')
        ->where('r.day >= :now')
        ->setParameter('now', $now->format('Y-m-d'))
        ->orderBy('ABS(DATEDIFF(r.day, :targetDate))', 'ASC')
        ->setParameter('targetDate', $targetDate->format('Y-m-d'))
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();

    return $result ? $result['day'] : null;
}


//SQL natif qui ne reconnais pas fetch
public function findClosestAvailableDate(string $day): ?\DateTime
{
    $targetDate = (new \DateTime($day))->format('Y-m-d');
    $now = (new \DateTime())->format('Y-m-d');

    $conn = $this->getEntityManager()->getConnection();

    $sql = '
        SELECT r.day
        FROM reservations r
        WHERE r.day >= :now
        ORDER BY ABS(DATEDIFF(r.day, :targetDate)) ASC
        LIMIT 1
    ';

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'now' => $now,
        'targetDate' => $targetDate,
    ]);

    $result = $stmt->fetch();

    return $result ? new \DateTime($result['day']) : null;
