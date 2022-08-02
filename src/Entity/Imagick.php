<?php

namespace App\Entity;

use App\Repository\ImagickRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagickRepository::class)]
class Imagick
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $totalpages = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalpages(): ?int
    {
        return $this->totalpages;
    }

    public function setTotalpages(int $totalpages): self
    {
        $this->totalpages = $totalpages;

        return $this;
    }
}
