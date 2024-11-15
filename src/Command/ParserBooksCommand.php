<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'parser:books',
    description: 'Парсинг книг из JSON файла',
)]
class ParserBooksCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;

    public function __construct(EntityManagerInterface $entityManager, HttpClientInterface $httpClient)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
    }

    protected function configure(): void
    {
        $this->setDescription('Парсинг книг и категорий из внешнего источника');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $url = 'https://gitlab.grokhotov.ru/hr/symfony-test-vacancy/-/raw/main/books.json'; 
        try {
            $response = $this->httpClient->request('GET', $url);
        } catch (TransportExceptionInterface $e) {
            $io->error('Не удалось загрузить данные из источника: ' . $e->getMessage());
            return Command::FAILURE;
        }

        if ($response->getStatusCode() !== 200) {
            $io->error('Не удалось загрузить данные из источника.');
            return Command::FAILURE;
        }

        $booksData = $response->toArray();

        foreach ($booksData as $bookData) {
            if (!isset($bookData['isbn'])) {
                $io->warning('Книга без ISBN: ' . ($bookData['title'] ?? 'Без названия'));
                continue;
            }

            $existingBook = $this->entityManager->getRepository(Book::class)->findOneBy(['isbn' => $bookData['isbn']]);

            if ($existingBook) {
                $io->note(sprintf('Книга с ISBN %s уже существует. Пропускаем.', $bookData['isbn']));
                continue;
            }

            $book = new Book();
            $book->setTitle($bookData['title'])
                ->setIsbn($bookData['isbn'])
                ->setPageCount($bookData['pageCount'] ?? null)
                ->setShortDescription($bookData['shortDescription'] ?? '')
                ->setLongDescription($bookData['longDescription'] ?? '')
                ->setStatus($bookData['status'] ?? 'available');

            // Загрузка изображения книги
            $imageUrl = $bookData['thumbnailUrl'] ?? null;
            if ($imageUrl) {
                try {
                    $imageResponse = $this->httpClient->request('GET', $imageUrl);

                    if ($imageResponse->getStatusCode() !== 200) {
                        $io->note('Не удалось скачать изображение для книги: ' . $bookData['title'] . ' (HTTP ' . $imageResponse->getStatusCode() . ')');
                        return Command::FAILURE;
                    }

                    $imageContent = $imageResponse->getContent();
                    $imagePath = 'public/uploads/image_' . basename($imageUrl);

                    file_put_contents($imagePath, $imageContent); 
                    $book->setImage($imagePath);
                } catch (TransportExceptionInterface $e) {
                    $io->note('Не удалось скачать изображение для книги: ' . $bookData['title']);
                }
            } else {
                $io->note('Нет изображения для книги: ' . $bookData['title']);
            }
            
            if (isset($bookData['publishedDate']['$date'])) {
                try {
                    $publishedDate = new \DateTime($bookData['publishedDate']['$date']);
                    $book->setPublishedDate($publishedDate);
                } catch (\Exception $e) {
                    $io->note('Некорректная дата для книги: ' . $bookData['title']);
                }
            }

            if (!empty($bookData['categories'])) {
                foreach ($bookData['categories'] as $categoryName) {
                    $category = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => $categoryName]);

                    if (!$category) {
                        $category = new Category();
                        $category->setName($categoryName);
                        $this->entityManager->persist($category);
                    }

                    $book->addCategory($category);
                }
            } else {
                $noveltyCategory = $this->entityManager->getRepository(Category::class)->findOneBy(['name' => 'Новинки']);

                if (!$noveltyCategory) {
                    $noveltyCategory = new Category();
                    $noveltyCategory->setName('Новинки');
                    $this->entityManager->persist($noveltyCategory);
                }

                $book->addCategory($noveltyCategory);
            }

            $this->entityManager->persist($book);
        }

        $this->entityManager->flush();

        $io->success('Парсинг книг завершен!');
        return Command::SUCCESS;
    }
}
