<?php

namespace App\Repository;

use App\Entity\ImportFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ImportFile>
 */
class ImportFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImportFile::class);
    }

    public function findOneByName(string $fileName): ?ImportFile
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.fileName = :val')
            ->setParameter('val', $fileName)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return ImportFile[] Returns an array of ImpoortFile objects
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

//    public function findOneBySomeField($value): ?ImportFile
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
