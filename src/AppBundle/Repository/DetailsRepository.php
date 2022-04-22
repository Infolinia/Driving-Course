<?php

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

/**
 * DetailsRepository
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class DetailsRepository extends EntityRepository
{


    public function findResults($lastName)
    {

        $q = $this->createQueryBuilder('a')
            ->join('a.owner', 'c')
            ->where('c.roles LIKE :roles')
            ->andWhere('a.lastName = :lastName')
            ->setParameter('roles', '%"ROLE_INSTRUCTOR"%')
            ->setParameter('lastName', $lastName)
            ->getQuery()->getResult();

        return $q;
    }
}
