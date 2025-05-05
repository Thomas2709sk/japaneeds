<?php

namespace App\Entity;

use App\Entity\Users;
use App\Repository\GuidesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuidesRepository::class)]
class Guides
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $nb_places = null;

    #[ORM\Column(length: 255)]
    private ?string $languages = null;

    #[ORM\Column]
    private ?bool $smoking = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'guides')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Specialities $speciality = null;

    
    #[ORM\OneToOne(targetEntity: Users::class, inversedBy: "guide",cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false)]
    private ?Users $user = null;

    /**
     * @var Collection<int, Cities>
     */
    #[ORM\ManyToMany(targetEntity: Cities::class, inversedBy: 'guides')]
    private Collection $cities;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prefernces = null;

    /**
     * @var Collection<int, Reservations>
     */
    #[ORM\OneToMany(targetEntity: Reservations::class, mappedBy: 'guide', orphanRemoval: true)]
    private Collection $reservations;

    /**
     * @var Collection<int, Reviews>
     */
    #[ORM\OneToMany(targetEntity: Reviews::class, mappedBy: 'guide', orphanRemoval: true)]
    private Collection $reviews;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
        $this->reservations = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nb_places;
    }

    public function setNbPlaces(int $nb_places): static
    {
        $this->nb_places = $nb_places;

        return $this;
    }

    public function getLanguages(): ?string
    {
        return $this->languages;
    }

    public function setLanguages(string $languages): static
    {
        $this->languages = $languages;

        return $this;
    }

    public function isSmoking(): ?bool
    {
        return $this->smoking;
    }

    public function setSmoking(bool $smoking): static
    {
        $this->smoking = $smoking;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSpeciality(): ?Specialities
    {
        return $this->speciality;
    }

    public function setSpeciality(?Specialities $speciality): static
    {
        $this->speciality = $speciality;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Cities>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(Cities $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
        }

        return $this;
    }

    public function removeCity(Cities $city): static
    {
        $this->cities->removeElement($city);

        return $this;
    }

    public function getPrefernces(): ?string
    {
        return $this->prefernces;
    }

    public function setPrefernces(?string $prefernces): static
    {
        $this->prefernces = $prefernces;

        return $this;
    }

    /**
     * @return Collection<int, Reservations>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservations $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setGuide($this);
        }

        return $this;
    }

    public function removeReservation(Reservations $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getGuide() === $this) {
                $reservation->setGuide(null);
            }
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
            $review->setGuide($this);
        }

        return $this;
    }

    public function removeReview(Reviews $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getGuide() === $this) {
                $review->setGuide(null);
            }
        }

        return $this;
    }
}
