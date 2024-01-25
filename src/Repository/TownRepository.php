<?php

namespace App\Repository;

use App\Entity\Town;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Town>
 *
 * @method Town|null find($id, $lockMode = null, $lockVersion = null)
 * @method Town|null findOneBy(array $criteria, array $orderBy = null)
 * @method Town[]    findAll()
 * @method Town[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TownRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Town::class);
    }

    public function add(Town $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Town $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getDatatableData($start, $length, $orders, $search, $columns)
    {
        // Create Main Query
        $query = $this->createQueryBuilder('town');

        // Create Count Query
        $countQuery = $this->createQueryBuilder('town');
        $countQuery->select('COUNT(town)');

        // Create joins
        $query
            ->leftJoin('town.department', 'department')
            ->addSelect('department')
            ->leftJoin('department.region', 'region')
            ->addSelect('region');
        $countQuery
            ->leftJoin('town.department', 'department')
            ->leftJoin('department.region', 'region');

        // Fields Search
        foreach ($columns as $i => $column)
        {
            if ($column['search']['value'] != '')
            {
                // $searchValue is what we are looking for
                $searchValue = $column['search']['value'];
                $where = '';

                // $column['name'] is the name of the column as sent by the JS
                if ( $column['name'] == 'name' ) {
                    $where = 'town.name LIKE :param'.$i;
                }
                else if ( $column['name'] == 'postalCode' ) {
                    $where = 'town.postalCode LIKE :param'.$i;
                }
                else if ( $column['name'] == 'department' ) {
                    $where = 'department.name LIKE :param'.$i;
                }
                else if ( $column['name'] == 'region' ) {
                    $where = 'region.name LIKE :param'.$i;
                }

                if ( $where ) {
                    $query->andWhere($where);
                    $query->setParameter('param'.$i, '%'.$searchValue.'%');
                    $countQuery->andWhere($where);
                    $countQuery->setParameter('param'.$i, '%'.$searchValue.'%');
                }
            }
        }

        // Order
        foreach ($orders as $key => $order)
        {
            // $columnName is the name of the order column as sent by the JS
            $columnName = $columns[$order['column']]['name'];
            if ($columnName != '')
            {
                $orderColumn = null;

                switch($columnName)
                {
                    case 'name':
                    {
                        $orderColumn = 'town.name';
                        break;
                    }
                    case 'postalCode':
                    {
                        $orderColumn = 'town.postalCode';
                        break;
                    }
                    case 'department':
                    {
                        $orderColumn = 'department.name';
                        break;
                    }
                    case 'region':
                    {
                        $orderColumn = 'region.name';
                        break;
                    }
                }

                if ($orderColumn !== null)
                {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        $records = $query->getQuery()->getResult();
        // total number of filtered records
        $totalFiltered = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "records" 		  => $records,
            "totalFiltered" => $totalFiltered
        );
    }

//    /**
//     * @return Town[] Returns an array of Town objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Town
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
