<?php

namespace App\Form;

use App\Entity\Book;  // Import the Book entity class here
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Category;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Название книги',
            ])
            ->add('author', TextType::class, [
                'label' => 'Автор',
            ])
            ->add('pageCount', IntegerType::class, [
                'label' => 'Количество страниц',
                'required' => true,
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN (артикул)',
                'required' => true,
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Категория',
                'placeholder' => 'Выберите категорию',
            ])
            ->add('coverImageFile', VichImageType::class, [
                'required' => false,
                'allow_delete' => true,
                'download_uri' => true,
                'label' => 'Изображение книги',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Book::class,
        ]);
    }
}

