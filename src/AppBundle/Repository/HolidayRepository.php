<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\EntityRepository;

class HolidayRepository extends EntityRepository {
    /**
     * @param User $user
     * @param $date
     * @return ArrayCollection
     */
    public function holidayIsExhist(User $user, $date) {
        $result = $this->createQueryBuilder('u')
            ->where('u.start_date = :date_start')
            ->OrWhere('u.finish_date = :date_end')
            ->setParameter('date_start', $date)
            ->setParameter('date_end',  $date)
            ->andWhere("u.owner = :owner")
            ->setParameter('owner', $user->getId())
            ->getQuery()->getResult();

        $collection = new ArrayCollection($result);
        return $collection;

    }
}