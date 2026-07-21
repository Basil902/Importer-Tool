<?php

namespace App\Repository;

use App\Entity\FileImport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileImport>
 */
class FileImportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileImport::class);
    }

    public function findOneByName(string $fileName): ?FileImport
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.fileName = :val')
            ->setParameter('val', $fileName)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return FileImport[] Returns an array of FileImport objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FileImport
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
