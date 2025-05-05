
{% if app.user.guide is not empty %}
<li><a href="">tesf</a></li>
{% endif %}

<a href="https://www.google.com/maps/search/?api=1&query={{ reservation.pointDeRencontre }}" target="_blank">
    Voir sur Google Maps
</a>>

 #[Route('/', name: 'index')]
    public function index(
        ReservationsRepository $reservationsRepository,
        UsersRepository $usersRepository, ReviewsRepository $reviewsRepository
    ): Response {

        
        // Récupérez les utilisateurs (guides)
        $users = $usersRepository->findBy([], null, 3);
        

           // Récupérer toutes les réservations
    $reservations = $reservationsRepository->findAll();

    // Associer les notes moyennes aux guides
    foreach ($reservations as $reservation) {
        $guideId = $reservation->getGuide()->getId();
        $averageRating = $reviewsRepository->getAverageRatingForGuide($guideId);
        $reservation->averageRating = $averageRating; // Propriété temporaire
    }

    // Afficher les résultats sur une vue dédiée
    return $this->render('reservations/index.html.twig', [
        'reservations' => $reservations,
        'users' => $users,
    ]);
    }


    {% block body %}
    <main>
        <h1>Réservations</h1>
        <section class="container">
            <h2>Les réservations disponibles</h2>
            {% for reservation in reservations %}
                <div class="container mt-5 col-lg-6 col-md-6 col-sm-10">
                    <div class="card shadow-lg card-guide">
                        <a href="{{ path('app_reservations_details', {id: reservation.id}) }}" target="_blank">
                            <div class="card-body">
                                <div class="row mb-2 border-bottom pb-2">
                                    <div class="col-lg-4">
                                        <strong>Date :</strong>
                                        {{ reservation.day.format('d/m/Y') }}
                                    </div>
                                    <div class="col-lg-4">
                                        <strong>Départ :</strong>
                                        {% if reservation.begin is not null %}
                                            <span>{{ reservation.begin|date('H:i') }}</span>
                                        {% else %}
                                            <p>Non défini</p>
                                        {% endif %}
                                    </div>
                                    <div class="col-lg-4">
                                        <strong>Fin :</strong>
                                        {% if reservation.end is not null %}
                                            <span>{{ reservation.end|date('H:i') }}</span>
                                        {% else %}
                                            <p>Non défini</p>
                                        {% endif %}
                                    </div>
                                    <div class="col-lg-4">
                                        <strong>Ville :</strong>
                                        {{ reservation.city.name }}
                                    </div>
                                    <div class="col-lg-4">
                                        <strong>Repas compris :</strong>
                                        {% if reservation.meal %}
                                            Oui
                                        {% else %}
                                            Non
                                        {% endif %}
                                    </div>
                                    <div class="col-lg-4">
                                        <strong>Place restante(s) :</strong>
                                        {{ reservation.placesDispo }}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-2 d-flex align-items-center">
                                <div class="ms-3">
                                    <img src="{{ reservation.guide.user.picture ? asset('uploads/users/mini/40x40-' ~ reservation.guide.user.picture) : asset('assets/images/favicon.png') }}" class="rounded-circle" alt="Photo de profil"/>
                                </div>
                                <div class="ms-1">
                                    <strong>{{ reservation.guide.user.pseudo }}</strong>
                                </div>
                                <div class="ms-2">
                                    <span class="fw-bold me-2" id="guide-note">
                                        {% if reservation.averageRating is not null %}
                                            {{ reservation.averageRating|number_format(1) }}/5
                                        {% else %}
                                            -/5
                                        {% endif %}
                                    </span>
                                </div>
                                <div class="ms-3">
                                    <strong>Prix :</strong>
                                    {{ reservation.price }}
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            {% endfor %}
        </section>
        <section>
            <h2>Nos guides</h2>
            <ul>
                {% for user in users %}
                    <li>
                        <img src="{{ user.picture ? asset('uploads/users/mini/40x40-' ~ user.picture) : asset('assets/images/favicon.png') }}" alt="Photo de profil"/>
                        {{ user.pseudo }}
                    </li>
                {% endfor %}
            </ul>
        </section>
    </main>
{% endblock %}


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
}