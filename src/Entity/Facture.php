<?php

namespace App\Entity;

use App\Repository\FactureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FactureRepository::class)]
class Facture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $Ref = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(length: 20)]
    private ?string $etat = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    public ?\DateTimeInterface $Date_de_paiment = null;

    #[ORM\ManyToOne(inversedBy: 'Factures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'facturiers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Facturier $facturier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->Ref;
    }

    public function setRef(string $Ref): self
    {
        $this->Ref = $Ref;

        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getDateDePaiment(): ?\DateTimeInterface
    {
        return $this->Date_de_paiment;
    }

    public function setDateDePaiment(\DateTimeInterface $Date_de_paiment): self
    {
        $this->Date_de_paiment = $Date_de_paiment;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFacturier(): ?Facturier
    {
        return $this->facturier;
    }

    public function setFacturier(?Facturier $Facturier): self
    {
        $this->facturier = $Facturier;

        return $this;
    }
    public function __toString(): string
    {
        return $this->Date_de_paiment->format('d-m-Y');
    }
}
