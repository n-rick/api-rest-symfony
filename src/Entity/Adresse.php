<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\AdresseRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AdresseRepository::class)]
#[ApiResource()]
class Adresse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["personne:read", "adresse:read"])]
    private $id;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    #[Groups(["personne:read", "adresse:read"])]
    private $rue;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    #[Groups(["personne:read", "adresse:read"])]
    private $codePostal;

    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    #[Groups(["personne:read", "adresse:read"])]
    private $ville;

    #[ORM\ManyToMany(targetEntity: Personne::class, mappedBy: 'adresses', cascade: ['persist'])]
    #[Groups(["adresse:read"])]
    private $personnes;

    public function __construct()
    {
        $this->personnes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * @return Collection<int, Personne>
     */
    public function getPersonnes(): Collection
    {
        return $this->personnes;
    }

    public function addPersonne(Personne $personne): self
    {
        if (!$this->personnes->contains($personne)) {
            $this->personnes[] = $personne;
            $personne->addAdress($this);
        }

        return $this;
    }

    public function removePersonne(Personne $personne): self
    {
        if ($this->personnes->removeElement($personne)) {
            $personne->removeAdress($this);
        }

        return $this;
    }
}
