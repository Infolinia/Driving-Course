<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository {
    /**
     * @param $email
     * @return User
     * @throws NonUniqueResultException
     */
    public function loadUserByEmail($email) {
        return $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $token
     * @return User
     * @throws NonUniqueResultException
     */
    public function loadUserByToken($token) {
        return $this->createQueryBuilder('u')
            ->where('u.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param $role
     * @return ArrayCollection
     */
    public function findByRole($role) {
        $result = $this->createQueryBuilder('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%"' . $role . '"%')
            ->getQuery()->getResult();

        $collection = new ArrayCollection($result);
        return $collection;
    }
}