<?php

namespace App\Entity;

use App\Repository\CitiesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CitiesRepository::class)]
class Cities
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $name = null;

    #[ORM\Column(length: 60)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Guides>
     */
    #[ORM\ManyToMany(targetEntity: Guides::class, mappedBy: 'cities')]
    private Collection $guides;

    /**
     * @var Collection<int, Reservations>
     */
    #[ORM\OneToMany(targetEntity: Reservations::class, mappedBy: 'city')]
    private Collection $reservations;

    public function __construct()
    {
        $this->guides = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Guides>
     */
    public function getGuides(): Collection
    {
        return $this->guides;
    }

    public function addGuide(Guides $guide): static
    {
        if (!$this->guides->contains($guide)) {
            $this->guides->add($guide);
            $guide->addCity($this);
        }

        return $this;
    }

    public function removeGuide(Guides $guide): static
    {
        if ($this->guides->removeElement($guide)) {
            $guide->removeCity($this);
        }

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
            $reservation->setCity($this);
        }

        return $this;
    }

    public function removeReservation(Reservations $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getCity() === $this) {
                $reservation->setCity(null);
            }
        }

        return $this;
    }
}
