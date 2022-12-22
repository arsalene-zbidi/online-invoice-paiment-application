<?php

namespace App\Entity;

use App\Repository\CarteBanquaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CarteBanquaireRepository::class)]
class CarteBanquaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 16)]
    private ?string $num_compte = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 3)]
    private ?string $Cvv2 = null;

    #[ORM\ManyToOne(inversedBy: 'carteBanquaires')]
    private ?CompteBancaire $CompteBanquaire = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumCompte(): ?string
    {
        return $this->num_compte;
    }

    public function setNumCompte(string $num_compte): self
    {
        $this->num_compte = $num_compte;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCvv2(): ?string
    {
        return $this->Cvv2;
    }

    public function setCvv2(string $Cvv2): self
    {
        $this->Cvv2 = $Cvv2;

        return $this;
    }

    public function getCompteBanquaire(): ?CompteBancaire
    {
        return $this->CompteBanquaire;
    }

    public function setCompteBanquaire(?CompteBancaire $CompteBanquaire): self
    {
        $this->CompteBanquaire = $CompteBanquaire;

        return $this;
    }
}
