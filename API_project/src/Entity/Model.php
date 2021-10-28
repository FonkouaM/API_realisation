<?php

namespace App\Entity;

use Gedmo\Timestampable\Traits\TimestampableEntity;
use App\Repository\ModelRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Timestampable;
/**
 * @ORM\Entity(repositoryClass=ModelRepository::class)
 */
class Model
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    // public function getId(): ?int
    // {
    //     return $this->id;
    // }


    // /**
    //  * @ORM\Column(type="datetime")
    //  * @Gedmo\Timestampable(on="create")
    //  */
    // private $createdAt;

    //  /**
    //  * @ORM\Column(type="datetime")
    //  * @Gedmo\Timestampable(on="update")
    //  */
    // private $updatedAt;

}
