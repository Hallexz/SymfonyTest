<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/', name: 'category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findBy(['parent' => null]);

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/{id}', name: 'category_show')]
    public function show(int $id, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);

        if ($category->getSubcategories()->isEmpty()) {
            $books = $category->getBooks();
            return $this->render('category/books.html.twig', [
                'category' => $category,
                'books' => $books,
            ]);
        }

        return $this->render('category/subcategories.html.twig', [
            'category' => $category,
            'subcategories' => $category->getSubcategories(),
        ]);
    }
}
