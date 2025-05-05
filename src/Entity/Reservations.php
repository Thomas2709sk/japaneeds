<?php

namespace App\Entity;

use App\Repository\ReservationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsRepository::class)]
class Reservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Guides $guide = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $day = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    private \DateTimeInterface $begin;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: false)]
    private \DateTimeInterface $end;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cities $city = null;

    #[ORM\Column]
    private ?bool $meal = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private int $placesDispo; 

    #[ORM\Column]
    private string $reservNumber; 

    #[ORM\Column(type: 'string', length: 100, columnDefinition: "ENUM('A venir', 'En cours', 'Fini', 'Confirmé', 'Vérification par la plateforme') NOT NULL DEFAULT 'A venir'")]
    private $status = 'A venir';

    #[ORM\Column]
    private ?string $address = null; 

    /**
     * @var Collection<int, Users>
     */
    #[ORM\ManyToMany(targetEntity: Users::class, mappedBy: 'reservations')]
    private Collection $users;

    /**
     * @var Collection<int, Reviews>
     */
    #[ORM\OneToMany(targetEntity: Reviews::class, mappedBy: 'reservation')]
    private Collection $reviews;

    public function __construct()
    {
        // Génère un identifiant unique pour la réservation
        $this->reservNumber = 'RES#' . substr(bin2hex(random_bytes(4)), 0, 8);;
        $this->users = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuide(): ?Guides
    {
        return $this->guide;
    }

    public function setGuide(?Guides $guide): static
    {
        $this->guide = $guide;

        return $this;
    }

    public function getDay(): ?\DateTimeInterface
    {
        return $this->day;
    }

    public function setDay(\DateTimeInterface $day): static
    {
        $this->day = $day;

        return $this;
    }

    public function getBegin(): ?\DateTimeInterface
    {
        return $this->begin;
    }

    public function setBegin(\DateTimeInterface $begin): static
    {
        $this->begin = $begin;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getCity(): ?Cities
    {
        return $this->city;
    }

    public function setCity(?Cities $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function isMeal(): ?bool
    {
        return $this->meal;
    }

    public function setMeal(bool $meal): static
    {
        $this->meal = $meal;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Users>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(Users $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addReservation($this);
        }

        return $this;
    }

    public function removeUser(Users $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeReservation($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Reviews>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Reviews $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setReservation($this);
        }

        return $this;
    }

    public function removeReview(Reviews $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getReservation() === $this) {
                $review->setReservation(null);
            }
        }

        return $this;
    }

    /**
     * Get the value of placesDispo
     */ 
    public function getPlacesDispo(): int
    {
        return $this->placesDispo;
    }

    /**
     * Set the value of placesDispo
     *
     * @return  self
     */ 
    public function setPlacesDispo(int $placesDispo): static
    {
        $this->placesDispo = $placesDispo;

        return $this;
    }

    /**
     * Get the value of reservNumber
     */ 
    public function getReservNumber()
    {
        return $this->reservNumber;
    }

    /**
     * Set the value of reservNumber
     *
     * @return  self
     */ 
    public function setReservNumber(string $reservNumber): self
    {
        $this->reservNumber = $reservNumber;

        return $this;
    }

    /**
     * Get the value of status
     */ 
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the value of address
     */ 
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */ 
    public function setAddress(string $address)
    {
        $this->address = $address;

        return $this;
    }
}
