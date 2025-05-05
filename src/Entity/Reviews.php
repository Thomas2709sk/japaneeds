<?php

namespace App\Entity;

use App\Repository\ReviewsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReviewsRepository::class)]
class Reviews
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $commentary = null;

    #[ORM\Column(type: 'boolean')]
    private $validate = false;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Users $user = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Guides $guide = null;

    #[ORM\ManyToOne(inversedBy: 'reviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservations $reservation = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getCommentary(): ?string
    {
        return $this->commentary;
    }

    public function setCommentary(string $commentary): static
    {
        $this->commentary = $commentary;

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

    public function getGuide(): ?Guides
    {
        return $this->guide;
    }

    public function setGuide(?Guides $guide): static
    {
        $this->guide = $guide;

        return $this;
    }

    public function getReservation(): ?Reservations
    {
        return $this->reservation;
    }

    public function setReservation(?Reservations $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }

    /**
     * Get the value of isValidated
     */ 
    public function getValidate()
    {
        return $this->validate;
    }

    /**
     * Set the value of isValidated
     *
     * @return  self
     */ 
    public function setValidate($validate)
    {
        $this->validate = $validate;

        return $this;
    }
}
