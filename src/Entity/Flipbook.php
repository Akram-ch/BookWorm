<?php

namespace App\Entity;

use App\Repository\FlipbookRepository;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use thiagoalessio\TesseractOCR\TesseractOCR;

#[ORM\Entity(repositoryClass: FlipbookRepository::class)]
class Flipbook
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;


    public function __construct($totalPages)
    {
        if ($totalPages < 10)
        {
            $this->totalPages = $totalPages;
        }
        else
            $this->totalPages = 10;
        $this->bookmark = 0;
    }

    #[ORM\Column]
    private ?int $totalPages = null;

    #[ORM\Column]
    private ?int $bookmark = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    public function setTotalPages(int $totalPages): self
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    public function flip($title)
    {
        $images = array();
        $i = 0;
        while (file_exists(dirname(__DIR__,2)."/public/images/$title/converted_$i.jpg"))
        {
            array_push($images, "images/$title/converted_$i.jpg");
            $i++;
        }
        if (!file_exists(dirname(__DIR__, 2)."/public/text/$title"))
            mkdir(dirname(__DIR__,2)."/public/text/$title", 0777, true);
        $i = 0;
        foreach($images as $image)
        {
            if (!file_exists(dirname(__DIR__,2)."/public/text/$title/text_$i.txt"))
            {
                $tesseract = new TesseractOCR(dirname(__DIR__,2)."/public/$image");
                $file = dirname(__DIR__,2)."/public/text/$title/text_$i.txt";
                try{
                    file_put_contents($file, $tesseract->run());
                }
                catch(Exception $e){
                    continue;                    
                }
            }
            $i++;
        }
        return $images;
    }

    public function getBookmark(): ?int
    {
        return $this->bookmark;
    }

    public function setBookmark(int $bookmark): self
    {
        $this->bookmark = $bookmark;

        return $this;
    }
}

