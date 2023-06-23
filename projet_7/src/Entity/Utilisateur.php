<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class)
 */
class Utilisateur
{
    use \App\Traits\ActivableBoolean;
    use \App\Traits\Timestampable;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getClient"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=client::class, inversedBy="utilisateurs")
     * @Groups({"getClient"})
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"getClient", "getUtilisateurDetail"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"getClient", "getUtilisateurDetail"})
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique = true)
     * @Groups({"getClient", "getUtilisateurDetail"})
     */
    private $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?client
    {
        return $this->client;
    }

    public function setClient(?client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
