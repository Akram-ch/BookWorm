<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\UploadType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Imagick;
use Symfony\Component\HttpFoundation\Request;


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


    #[Route('/gallery/{document}', name:'book')]
    public function reader($document):Response
    {
        $repo = $this->em->getRepository(Book::class);
        $book = $repo->findOneBy(['document' => $document]);
        $cover = $book->getCoverArt();
        $path = dirname(__DIR__,2)."/public/uploads/".$document;
        $totalpages = $this->countPages($path);
        $images = array();
        $fullpath = dirname(__DIR__,2)."/public/temp/";
        array_map('unlink', glob( "$fullpath*.jpg"));
        $imagick = new Imagick();
        for ($i = 0; $i < $totalpages; $i++){

            $imagick->readImage(dirname(__DIR__,2)."/public/uploads/".$document."[$i]");
            $imagick->setResolution(300,150);
            $imagick->writeImage(dirname(__DIR__,2)."/public/temp/converted_$i.jpg");
            array_push($images, "temp/converted_$i.jpg");
            if ($i > 30)
                break;
        }


        return $this->render('reader.html.twig', ['images'=>$images, 'cover'=>$cover]);
    }

    #[Route('/title', name:'reader')]
    public function flipbook():Response {
        $imagick = new Imagick();
        return $this->render('reader.html.twig');
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
            return $this->redirectToRoute('gallery');            
        }
        return $this->render('upload.html.twig', ['form'=>$form->createView()]);
    }
    #[Route('/test', name:'test')]
    public function test(): Response
    {
        dd(pdfinfo(dirname(__DIR__,2)."/public/uploads/rickroll.pdf"));
    }

    function countPages($path) {
        $pdftext = file_get_contents($path);
        $num = preg_match_all("/\/Page\W/", $pdftext, $dummy);
        return $num;
      }
}
