<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Book;
use App\Entity\File;

class BookFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        for ($i = 0; $i < 10; $i++){
            $book = new Book();
            $book->setTitle("book".$i)
                ->setAuthor("Author".$i)
                ->setDocument('placeholder')
                ->setCoverArt("https://d827xgdhgqbnd.cloudfront.net/wp-content/uploads/2016/04/09121712/book-cover-placeholder.png");
            $manager->persist($book);
        }
        $manager->flush();
    }
}
