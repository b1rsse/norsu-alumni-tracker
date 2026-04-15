<?php

namespace App\Repository;

use App\Entity\Alumni;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Returns total alumni grouped by employment status.
     *
     * @return array<int, array{employmentStatus: string, total: int}>
     */
    public function countGroupedByEmploymentStatus(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select("COALESCE(NULLIF(TRIM(a.employmentStatus), ''), :unknown) AS employmentStatus")
            ->addSelect('COUNT(a.id) AS total')
            ->setParameter('unknown', 'Unknown')
            ->groupBy('employmentStatus')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): array => [
            'employmentStatus' => (string) $row['employmentStatus'],
            'total' => (int) $row['total'],
        ], $rows);
    }

    /**
     * Returns employment statistics as an associative array:
     * [
     *   'Employed' => 120,
     *   'Unemployed' => 35,
     *   'Self-Employed' => 22,
     * ]
     *
     * @return array<string, int>
     */
    public function getEmploymentStats(): array
    {
        $rows = $this->createQueryBuilder('a')
            ->select("COALESCE(NULLIF(TRIM(a.employmentStatus), ''), :unknown) AS employmentStatus")
            ->addSelect('COUNT(a.id) AS total')
            ->setParameter('unknown', 'Unknown')
            ->groupBy('employmentStatus')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $stats = [];
        foreach ($rows as $row) {
            $stats[(string) $row['employmentStatus']] = (int) $row['total'];
        }

        return $stats;
    }

    /**
     * Returns alumni tracer totals for dashboard pie chart.
     * Treats legacy "Fully Traced" values as TRACED for backward compatibility.
     *
     * @return array{TRACED: int, UNTRACED: int}
     */
    public function countTracedVsUntraced(): array
    {
        $row = $this->createQueryBuilder('a')
            ->select("SUM(CASE WHEN UPPER(COALESCE(NULLIF(TRIM(a.tracerStatus), ''), 'UNTRACED')) IN ('TRACED', 'FULLY TRACED') THEN 1 ELSE 0 END) AS traced")
            ->addSelect("SUM(CASE WHEN UPPER(COALESCE(NULLIF(TRIM(a.tracerStatus), ''), 'UNTRACED')) IN ('TRACED', 'FULLY TRACED') THEN 0 ELSE 1 END) AS untraced")
            ->andWhere('a.deletedAt IS NULL')
            ->getQuery()
            ->getSingleResult();

        return [
            'TRACED' => (int) ($row['traced'] ?? 0),
            'UNTRACED' => (int) ($row['untraced'] ?? 0),
        ];
    }

    /**
     * Search alumni by Batch Year, Campus (mapped to college), and Course.
     */
    public function searchByBatchCampusCourse(?int $batchYear, ?string $campus, ?string $course): QueryBuilder
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.deletedAt IS NULL');

        if ($batchYear !== null) {
            $qb->andWhere('a.yearGraduated = :batchYear')
                ->setParameter('batchYear', $batchYear);
        }

        if ($campus !== null && trim($campus) !== '') {
            $qb->andWhere('a.college LIKE :campus')
                ->setParameter('campus', '%' . trim($campus) . '%');
        }

        if ($course !== null && trim($course) !== '') {
            $qb->andWhere('a.course LIKE :course')
                ->setParameter('course', '%' . trim($course) . '%');
        }

        return $qb->orderBy('a.lastName', 'ASC');
    }
}
