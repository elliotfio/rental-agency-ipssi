<?php

namespace App\Repository;

use App\Entity\Vehicle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Vehicle>
 */
class VehicleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vehicle::class);
    }

    /**
     *
     *
     * @param array $criteria
     * @return Vehicle[]
     */
    public function findByCriteria(array $criteria): array
    {
        $qb = $this->createQueryBuilder('v');

        if (!empty($criteria['brand'])) {
            $qb->andWhere('v.brand LIKE :brand')
                ->setParameter('brand', '%' . $criteria['brand'] . '%');
        }

        if (!empty($criteria['dailyPrice'])) {
            $qb->andWhere('v.dailyPrice <= :maxPrice')
                ->setParameter('maxPrice', $criteria['dailyPrice']);
        }

        if (isset($criteria['available'])) {
            $qb->andWhere('v.available = :available')
                ->setParameter('available', $criteria['available']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * 
     *
     * @param Vehicle $vehicle
     * @return Vehicle[]
     */
    public function findSimilarVehicles(Vehicle $vehicle): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.id != :vehicleId') 
            ->andWhere('v.brand = :brand') 
            ->andWhere('v.dailyPrice BETWEEN :minPrice AND :maxPrice') 
            ->setParameter('vehicleId', $vehicle->getId())
            ->setParameter('brand', $vehicle->getBrand())
            ->setParameter('minPrice', $vehicle->getDailyPrice() * 0.8) 
            ->setParameter('maxPrice', $vehicle->getDailyPrice() * 1.2)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

    /**
     * 
     *
     * @param int $userId
     * @param int $vehicleId
     * @return bool
     */
    public function hasUserRentedVehicle(int $userId, int $vehicleId): bool
    {
        return (bool) $this->createQueryBuilder('v')
            ->join('v.reservations', 'r')
            ->where('r.customer = :userId')
            ->andWhere('v.id = :vehicleId')
            ->setParameter('userId', $userId)
            ->setParameter('vehicleId', $vehicleId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 
     *
     * @param int $vehicleId
     * @return int
     */
    public function countReservations(int $vehicleId): int
    {
        return (int) $this->createQueryBuilder('v')
            ->select('COUNT(r.id)')
            ->join('v.reservations', 'r')
            ->where('v.id = :vehicleId')
            ->setParameter('vehicleId', $vehicleId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}