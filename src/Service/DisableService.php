<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class DisableService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    function disable($data, User $user)
    {
        $status = array();

        if ($data->getActiv() === 1) {
            $data->setActiv(2);
            $data->setApprovedBy($user);
            $status['status'] = true;
            $status['snack'] = 'Gelöscht';
        } else {
            $data->setActiv(1);
            $data->setApprovedBy($user);
            $status['status'] = true;
            $status['snack'] = 'Widerhergestellt';
        }
        $this->em->persist($data);
        $this->em->flush();
        return $status;
    }
}
