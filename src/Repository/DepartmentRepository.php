<?php

namespace App\Repository;

use App\Entity\Department;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    public function findActive()
    {
        return $this->createQueryBuilder('d')
            ->where('d.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCollege($college)
    {
        return $this->createQueryBuilder('d')
            ->where('d.college = :college')
            ->setParameter('college', $college)
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
