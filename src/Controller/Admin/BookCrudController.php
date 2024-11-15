<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{TextField, IntegerField, AssociationField, ImageField};

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextEditorField::new('description'),
            TextField::new('isbn', 'ISBN'),
            IntegerField::new('pages', 'Страниц'),
            ImageField::new('image', 'Изображение')
                ->setUploadDir('public/uploads/books') // Директория для загрузки изображений
                ->setUploadRootDir('public') // Корневая директория
                ->setRequired(false),
        ];
    }
}
