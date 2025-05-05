<?php

namespace App\Entity;

use App\Repository\SpecialitiesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpecialitiesRepository::class)]
class Specialities
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
    #[ORM\OneToMany(targetEntity: Guides::class, mappedBy: 'speciality')]
    private Collection $guides;

    public function __construct()
    {
        $this->guides = new ArrayCollection();
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
            $guide->setSpeciality($this);
        }

        return $this;
    }

    public function removeGuide(Guides $guide): static
    {
        if ($this->guides->removeElement($guide)) {
            // set the owning side to null (unless already changed)
            if ($guide->getSpeciality() === $this) {
                $guide->setSpeciality(null);
            }
        }

        return $this;
    }
}
