<?php

namespace App\Repository;

use App\Entity\Tasks;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tasks|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tasks|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tasks[]    findAll()
 * @method Tasks[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TasksRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tasks::class);
    }

    /**
     * @param $userId
     * @param $from
     * @param $to
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUserTasks($userId,$from,$to){
        $conn = $this->getEntityManager()->getConnection();
        $prepered = $conn->prepare('select t.title, t.comment, DATE_FORMAT(t.date, "%Y-%m-%d") as date, t.time_spent from tasks t where t.user_id=:userId and t.date BETWEEN :from AND :to');
        return $prepered->executeQuery([':userId'=>$userId,':from'=>$from,':to'=>$to])->fetchAllNumeric();
    }

    /**
     * @param $userId
     * @param $from
     * @param $to
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUserTasksTimeSpentSum($userId,$from,$to){
        $conn = $this->getEntityManager()->getConnection();
        $prepered = $conn->prepare('select SUM(t.time_spent) from tasks t where t.user_id=:userId and t.date BETWEEN :from AND :to');
        return $prepered->executeQuery([':userId'=>$userId,':from'=>$from,':to'=>$to])->fetchOne();
    }
}
