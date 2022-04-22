<?php

namespace AppBundle\Repository;


use AppBundle\Entity\Course;
use Doctrine\ORM\EntityRepository;

/**
 * RideRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class CourseRepository extends EntityRepository
{
    public function findAll()
    {
        return $this->findBy(array('enabled' => 1));
    }


    /**
     * @return array
     * @throws \Exception
     */
    public function findActiveExpired()
    {
        return $this->createQueryBuilder("a")
            ->where("a.enabled = :ednabled")
            ->setParameter("ednabled", Course::ENALBED)
            ->andWhere("a.finishTime < :now")
            ->setParameter("now", new \DateTime)
            ->getQuery()
            ->getResult();
    }
}