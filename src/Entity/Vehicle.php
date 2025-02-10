<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $brand = null;

    #[ORM\Column(length: 50)]
    private ?string $licencePlate = null;

    #[ORM\Column(type: "float")]
    #[Assert\Range(min: 100, max: 500, notInRangeMessage: "Le prix doit être entre 100 et 500 €.")]
    private ?float $dailyPrice = null;

    #[ORM\Column]
    private ?bool $available = null;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: Comment::class, cascade: ['remove'])]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'vehicle', targetEntity: VehicleImage::class, cascade: ['persist', 'remove'])]
    private Collection $images;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: "favorites")]
    private Collection $favorites;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->favorites = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;
        return $this;
    }

    public function getLicencePlate(): ?string
    {
        return $this->licencePlate;
    }

    public function setLicencePlate(string $licencePlate): static
    {
        $this->licencePlate = $licencePlate;
        return $this;
    }

    public function getDailyPrice(): ?float
    {
        return $this->dailyPrice;
    }

    public function setDailyPrice(float $dailyPrice): static
    {
        // Appliquer une réduction de 10% si le prix est supérieur à 400€
        if ($dailyPrice >= 400) {
            $dailyPrice *= 0.9; // Réduction de 10%
        }
        $this->dailyPrice = $dailyPrice;
        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): static
    {
        $this->available = $available;
        return $this;
    }

    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getAverageRating(): float
    {
        if ($this->comments->isEmpty()) {
            return 0;
        }

        $total = array_reduce($this->comments->toArray(), fn($sum, Comment $comment) => $sum + $comment->getRating(), 0);
        return round($total / count($this->comments), 1);
    }

    public function getTotalReservations(): int
    {
        return count($this->reservations);
    }

    public function getImages(): Collection
    {
        return $this->images;
    }

    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(User $user): void
    {
        if (!$this->favorites->contains($user)) {
            $this->favorites->add($user);
        }
    }

    public function removeFavorite(User $user): void
    {
        $this->favorites->removeElement($user);
    }

    public function isCurrentlyBooked(): bool
    {
        $now = new \DateTime();
        foreach ($this->reservations as $reservation) {
            if ($reservation->getStartDate() <= $now && $reservation->getEndDate() >= $now) {
                return true;
            }
        }
        return false;
    }

    public function canBeModified(): bool
    {
        return !$this->isCurrentlyBooked();
    }
}