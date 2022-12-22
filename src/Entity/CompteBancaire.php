<?php

namespace App\Entity;

use App\Repository\CompteBancaireRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompteBancaireRepository::class)]
class CompteBancaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $num_compte = null;

    #[ORM\Column(length: 30)]
    private ?string $Rib = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $solde = null;

    #[ORM\ManyToOne(inversedBy: 'comptesbancaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 3)]
    private ?string $Cvv2 = null;

    #[ORM\OneToMany(mappedBy: 'CompteBanquaire', targetEntity: CarteBanquaire::class)]
    private Collection $carteBanquaires;

    public function __construct()
    {
        $this->carteBanquaires = new ArrayCollection();
    }

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

    public function getRib(): ?string
    {
        return $this->Rib;
    }

    public function setRib(string $Rib): self
    {
        $this->Rib = $Rib;

        return $this;
    }

    public function getSolde(): ?string
    {
        return $this->solde;
    }

    public function setSolde(string $solde): self
    {
        $this->solde = $solde;

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

    public function getCvv2(): ?string
    {
        return $this->Cvv2;
    }

    public function setCvv2(string $Cvv2): self
    {
        $this->Cvv2 = $Cvv2;

        return $this;
    }

    /**
     * @return Collection<int, CarteBanquaire>
     */
    public function getCarteBanquaires(): Collection
    {
        return $this->carteBanquaires;
    }

    public function addCarteBanquaire(CarteBanquaire $carteBanquaire): self
    {
        if (!$this->carteBanquaires->contains($carteBanquaire)) {
            $this->carteBanquaires->add($carteBanquaire);
            $carteBanquaire->setCompteBanquaire($this);
        }

        return $this;
    }

    public function removeCarteBanquaire(CarteBanquaire $carteBanquaire): self
    {
        if ($this->carteBanquaires->removeElement($carteBanquaire)) {
            // set the owning side to null (unless already changed)
            if ($carteBanquaire->getCompteBanquaire() === $this) {
                $carteBanquaire->setCompteBanquaire(null);
            }
        }

        return $this;
    }
}
