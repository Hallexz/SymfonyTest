<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    // Метод для поиска книг по названию
    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.title LIKE :title')
            ->setParameter('title', '%' . $title . '%')
            ->orderBy('b.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Метод для пагинации списка книг
    public function findAllPaginated(int $page, int $limit): Pagerfanta
    {
        $queryBuilder = $this->createQueryBuilder('b')
            ->orderBy('b.title', 'ASC');

        // Настройка адаптера для пагинации
        $pagerfanta = new Pagerfanta(new DoctrineAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage($limit);
        $pagerfanta->setCurrentPage($page);

        return $pagerfanta;
    }
}
