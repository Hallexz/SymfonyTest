<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/book', name: 'book_create')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(BookType::class, new Book());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();

            if ($file) {
                $filename = $this->uploadBookImage($file);

                $book = $form->getData();
                $book->setImage($filename);

                $this->entityManager->persist($book);
                $this->entityManager->flush();

                return $this->redirectToRoute('books_index');
            }
        }

        return $this->render('book/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function uploadBookImage(UploadedFile $file): string
    {
        $filename = uniqid().'.'.$file->guessExtension();

        $file->move(
            $this->getParameter('book_images_directory'),
            $filename
        );

        return $filename;
    }
}
