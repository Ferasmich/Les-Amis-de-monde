<?php

namespace App\Entity;

use App\Repository\SubscriberRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid; // Import Uuid class

#[ORM\Entity(repositoryClass: SubscriberRepository::class)]
class Subscriber
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]  // Enforce unique email constraint
    private ?string $email = null;

    #[ORM\Column(type: "boolean")]
    private bool $isConfirmed = false;  // Default to subscribed

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $subscribedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $unsubscribeToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmationToken = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getIsConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function setIsConfirmed(bool $isConfirmed): static
    {
        $this->isConfirmed = $isConfirmed;

        return $this;
    }

    public function getSubscribedAt(): ?\DateTimeInterface
    {
        return $this->subscribedAt;
    }

    public function setSubscribedAt(?\DateTimeInterface $subscribedAt): static
    {
        $this->subscribedAt = $subscribedAt;

        return $this;
    }

    public function getUnsubscribeToken(): ?string
    {
        return $this->unsubscribeToken;
    }

    public function setUnsubscribeToken(?string $unsubscribeToken): static
    {
        $this->unsubscribeToken = $unsubscribeToken;

        return $this;
    }

    // Add a method to generate a unique unsubscribe token
    public function generateUnsubscribeToken(): void
    {
        $this->unsubscribeToken = (string) Uuid::uuid4();
    }
    
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    public function setConfirmationToken(?string $confirmationToken): static
    {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    // Add a method to generate a unique unsubscribe token
    public function generateConfirmationToken(): void
    {
        $this->confirmationToken = (string) Uuid::uuid4();
    }

}
