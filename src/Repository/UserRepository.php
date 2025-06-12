<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUsersPerDay(int $days = 30): array
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $classMetadata = $em->getClassMetadata(User::class);

        $tableName = $platform->quoteIdentifier($classMetadata->getTableName());
        $createdAtColumn = $platform->quoteIdentifier($classMetadata->getColumnName('createdAt'));

        $startDate = new \DateTime("-$days days");

        $sql = "
        SELECT CAST({$createdAtColumn} AS DATE) AS date, COUNT(*) AS count 
        FROM {$tableName} 
        WHERE {$createdAtColumn} >= :startDate 
        GROUP BY CAST({$createdAtColumn} AS DATE)
        ORDER BY date DESC
    ";

        return $conn->executeQuery(
            $sql,
            ['startDate' => $startDate],
            ['startDate' => \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE]
        )->fetchAllAssociative();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
