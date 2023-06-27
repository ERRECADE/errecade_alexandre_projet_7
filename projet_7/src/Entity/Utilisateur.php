<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Hateoas\Configuration\Annotation\Relation;


/**
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class) 
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "detail_utilisateur",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getClient"),
 *      attributes = {
 *          "comment" = "Lors de la redirection, Utiliser le POST pour voir le détail, PUT pour l'update enfin DELETE pour suprimez , pensez a mettre votre token !"
 *      }
 * ) 
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
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="utilisateurs")
     * @Groups({"getClient"})
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"getClient", "getUtilisateurDetail"})
     * @Serializer\SerializedName("name")
     * @Assert\NotBlank(message="Le nom de l'utilisateur est obligatoire")
     * @Assert\Length(
     *     min=1,
     *     max=255,
     *     minMessage="Le nom doit faire au moins {{ limit }} caractères",
     *     maxMessage="Le nom ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"getClient", "getUtilisateurDetail"})
     * @Serializer\SerializedName("prenom")
     * @Assert\NotBlank(message="Le prenom de l'utilisateur est obligatoire")
     * @Assert\Length(
     *     min=1,
     *     max=255,
     *     minMessage="Le prenom doit faire au moins {{ limit }} caractères",
     *     maxMessage="Le prenom ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     * @Groups({"getClient", "getUtilisateurDetail"})
     * @Serializer\SerializedName("email")
     * @Assert\NotBlank(message="L'email de l'utilisateur est obligatoire")
     * @Assert\Length(
     *     min=1,
     *     max=255,
     *     minMessage="L'email doit faire au moins {{ limit }} caractères",
     *     maxMessage="L'email ne peut pas faire plus de {{ limit }} caractères"
     * )
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
