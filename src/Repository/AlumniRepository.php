<?php

namespace App\Repository;

use App\Entity\Alumni;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Alumni>
 *
 * @method Alumni|null find($id, $lockMode = null, $lockVersion = null)
 * @method Alumni|null findOneBy(array $criteria, array $orderBy = null)
 * @method Alumni[]    findAll()
 * @method Alumni[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AlumniRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alumni::class);
    }

    // Add custom repository methods here if needed, e.g. search by course or batch.
}
