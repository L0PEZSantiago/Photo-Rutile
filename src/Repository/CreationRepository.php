<?php

namespace App\Repository;

use App\Entity\Creation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Creation>
 */
class CreationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Creation::class);
    }

//    /**
//     * @return Creation[] Returns an array of Creation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Creation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function searchByName(?string $query): array
    {
        return $this->search($query, null);
    }

    public function searchByTheme(?string $theme): array
    {
        return $this->search(null, $theme);
    }

    /**
     * @return Creation[]
     */
    public function search(?string $query, ?string $theme): array
    {
        $qb = $this->createQueryBuilder('c')
            ->orderBy('c.createdAt', 'DESC');

        $theme = $theme !== null ? trim($theme) : '';
        if ($theme !== '' && strcasecmp($theme, 'all') !== 0) {
            $qb
                ->innerJoin('c.theme', 't')
                ->andWhere('LOWER(t.slug) = LOWER(:themeSlug) OR LOWER(t.name) LIKE LOWER(:themeNameLike)')
                ->setParameter('themeSlug', $theme)
                ->setParameter('themeNameLike', '%' . $theme . '%');
        }

        if ($query !== null && trim($query) !== '') {
            $qb
                ->andWhere('LOWER(c.title) LIKE LOWER(:query)')
                ->setParameter('query', '%' . trim($query) . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
