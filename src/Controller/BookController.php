<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Flipbook;
use App\Entity\Reader;
use App\Form\ReaderFormType;
use App\Form\UploadType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Imagick;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
require_once(dirname(__DIR__,2)."/vendor/autoload.php");

class BookController extends AbstractController
{
    var $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/home', name: 'home')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

    #[Route('/gallery', name:'gallery')]
    public function showcase(): Response
    {
        $repo = $this->em->getRepository(Book::class);
        $books = $repo->findAll();
        return $this->render('showcase.html.twig',['books'=>$books]);
    }

    /**
     * @Route("/gallery/{document}", name="reader")
     */
    public function reader($document)
    {
        $repo = $this->em->getRepository(Book::class);
        $book = $repo->findOneBy(['document' => $document]);
        $path = dirname(__DIR__,2)."/public/uploads/".$document;
        $totalpages = $this->countPages($path);
        $cover = $book->getCoverArt();
        $title = $book->getTitle();
        $title = str_replace(' ','',$title);
        $flip = new Flipbook($totalpages);
        $images = $flip->flip($title);
        return $this->render('reader.html.twig', ['images'=>$images, 'cover'=>$cover, 'document'=>$document, 'title'=>$title]);
        

    }


    #[Route('/submit', name:'submit')]
    public function upload(Request $request): Response
    {
        $book = new Book();
        $form = $this->createForm(UploadType::class, $book);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            //dd( $request->files->get('upload')['file']);
            $file = $request->files->get('upload')['file'];
            $upload_directory = $this->getParameter('upload_directory');
            $filename = $book->getTitle().".pdf";
            $file->move($upload_directory,$filename);
            $book->setDocument($filename);
            $this->em->persist($book);
            $this->em->flush();
            $document = $book->getDocument();
            $path = dirname(__DIR__,2)."/public/uploads/".$document;
            $totalpages = $this->countPages($path);
            $title = $book->getTitle();
            $title = str_replace(' ','',$title);
            if (! file_exists(dirname(__DIR__,2)."/public/images/".$title))
            {
                mkdir(dirname(__DIR__,2)."/public/images/$title", 0777, true);
                $imagick = new Imagick();
                $imagick->setResolution(250,250);
                for ($i = 0; $i < $totalpages; $i++)
                {
                    $imagick->readImage(dirname(__DIR__,2)."/public/uploads/".$document."[$i]");
                    $imagick->writeImage(dirname(__DIR__,2)."/public/images/$title/converted_$i.jpg");
                    if ($i >= 8)
                        break;
                }
            }
            if (! file_exists(dirname(__DIR__,2)."/public/text/".$title))
            {
                mkdir(dirname(__DIR__,2)."/public/text/$title", 0777, true);
                // Initialize and load PDF Parser library 
                $parser = new \Smalot\PdfParser\Parser(); 
                $file = $path;
                // Parse pdf file using Parser library 
                $pdf = $parser->parseFile($file); 
                // Extract text from PDF 
                $textContent = $pdf->getText();
                $textFile = dirname(__DIR__,2)."/public/text/$title/$title.txt";
                file_put_contents($textFile,$textContent);
            }
            return $this->redirectToRoute('gallery');            
        }
        return $this->render('upload.html.twig', ['form'=>$form->createView()]);
    }

    #[Route('/about', name:'about')]
    public function about():Response
    {
        return $this->render('about.html.twig');
    }

    public function countPages($path) {
        $pdftext = file_get_contents($path);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
        return $num;
    }

    #[Route('/test', name:'test')]
    public function testing(): Response
    {
        return $this->render('test.html.twig');
    }

    #[Route('/gallery/{document}/read/more', name:'readmore')]
    public function readmore($document)
    {
        $path = dirname(__DIR__,2)."/public/uploads/".$document;
        $totalpages = $this->countPages($path);
        $imagick = new Imagick();
        $imagick->setResolution(250,250);
        $title = str_replace('.pdf','',$document);
        $title = str_replace(' ','',$title);
        $i = 0;
        $j = 0;
        while ($i < 10 && $j<$totalpages)
        {
            if (!file_exists(dirname(__DIR__,2)."/public/images/$title/converted_$j.jpg"))
            {
                $imagick->readImage(dirname(__DIR__,2)."/public/uploads/".$document."[$j]");
                $imagick->writeImage(dirname(__DIR__,2)."/public/images/$title/converted_$j.jpg");
                $i++;               
            }
            $j++;
        }
        return $this->redirectToRoute('reader', ['document'=>$document]);
    }

    #[Route('gallery/{document}/{page}', name:'speak')]
    public function speak($document, $page){
        $title = str_replace('.pdf','',$document);
        $title = str_replace(' ','',$title);
        $reader = new Reader();
        $text = file_get_contents(dirname(__DIR__,2)."/public/text/$title/text_$page.txt");
        if (!file_exists(dirname(__DIR__,2)."/public/audio/$title"))
            mkdir(dirname(__DIR__,2)."/public/audio/$title", 0777, true);
        if (!file_exists(dirname(__DIR__,2)."/public/audio/$title/page_$page.mp3"));
            $reader->textToSpeech($text,$title, $page);
        return $this->redirectToRoute('reader', ['document'=>$document]);
    }

    
}
