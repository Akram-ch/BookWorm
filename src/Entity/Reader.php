<?php

namespace App\Entity;

use App\Repository\ReaderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReaderRepository::class)]
class Reader
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column()]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    public $pages = null;

    #[ORM\Column]
    private ?int $totalPages = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function setPages(?int $pages)
    {
        $this->page = $pages;

        return $this;
    }
    
    public function textToSpeech($text, $title, $page)
    {
        $file = dirname(__DIR__,2)."/public/audio/$title/page_$page.mp3";
        $mp3 = "";
        $i = 0;
        while ($i < strlen($text) - 100)
        {
            $paragraph = substr($text, $i, 100);
            $paragraph = htmlspecialchars($paragraph);
            $paragraph = rawurlencode($paragraph);
            $i += 100;
            $audio = file_get_contents('https://translate.google.com/translate_tts?ie=UTF-8&client=gtx&q='.$paragraph.'&tl=en-IN');
            $mp3 = $mp3 . $audio;
        }
        $paragraph = substr($text, $i);
        $paragraph = htmlspecialchars($paragraph);
        $paragraph = rawurlencode($paragraph);
        $audio = file_get_contents('https://translate.google.com/translate_tts?ie=UTF-8&client=gtx&q='.$paragraph.'&tl=en-IN');
        $mp3 = $mp3 . $audio;
        file_put_contents($file, $mp3);
        return $file;        
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
}
