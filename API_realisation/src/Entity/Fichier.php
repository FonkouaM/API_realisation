<?php

namespace App\Entity;

// use App\Entity\Model;
// use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Utilisateur;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use App\Repository\FichierRepository;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Mapping\Annotation\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=FichierRepository::class)
 */
class Fichier
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("fichier:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("fichier:read")
     */
    private $Nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("fichier:read")
     */
    private $Description;
   
    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("fichier:read")
     * @Assert\NotBlank(message="Please, upload the Lien as a PDF file.")
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $Lien;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="fichiers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("fichier:read")
     */
    private $utilisateur;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, options={"default" : "actif"})
     */
    private $visible = 'actif';

    /**
     * @return \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @Groups("fichier:read")
     */
    private $createdAt;

     /**
      * @return \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @Groups("fichier:read")
     */
    private $updatedAt;

    public const VISIBLE_ARRAY = array(
        "ACTIF" => "actif",  
        "INACTIF" => "inactif"
    );
   
   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): self
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getLien(): ?string
    {
        return $this->Lien;
    } 
    
    public function setLien(string $lien): self
    {
        $this->Lien = $lien;

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }
   
    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getVisible(): ?string
    {
        return $this->visible;
    }

    public function setVisible(string $visible): self
    {
        $this->visible = ($visible === 'actif' || $visible === 'inactif')? 
        $visible : 'actif' ;

        return  $this;
    }

}
