<?php

declare(strict_types=1);

namespace Manuxi\SuluEventBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Manuxi\SuluEventBundle\Entity\Traits\ImageTrait;
use Manuxi\SuluEventBundle\Repository\LocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\Table(name: 'app_location')]
class Location
{
    public const RESOURCE_KEY = 'locations';
    public const FORM_KEY = 'location_details';
    public const LIST_KEY = 'locations';

    use ImageTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $street;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $number;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $postalCode;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $city;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $state;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $countryCode;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

}
