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
use Symfony\Component\Form\FormFactoryInterface;


class ApproveService
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager, FormFactoryInterface $formBuilder)
    {
        $this->em = $entityManager;
    }

    function approve($data, User $user)
    {
        $status = array();
        try {
            if ($data->getApproved()) {
                $data->setApproved(false);
                $data->setApprovedBy(null);
                $status['status'] = true;
                $status['snack'] = 'Freigabe entfernt';
            } else {
                $data->setApproved(true);
                $data->setApprovedBy($user);
                $status['status'] = true;
                $status['snack'] = 'Element freigegeben';
            }

            $this->em->persist($data);
            $this->em->flush();
            return $status;
        } catch (\Exception $e) {
            $status['status'] = false;
            $status['snack'] = 'FEHLER: Element konnte nicht freigegeben werden. Versuchen Sie es noch einmal.';
            return $status;
        }
    }
}
