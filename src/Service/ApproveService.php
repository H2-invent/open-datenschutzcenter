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
        $status['data'] = $data->getId();
        $status['clone'] = false;
        try {

            if ($data->getApproved() === true) {
                $newdata = clone $data;
                $data->setActiv(false);
                $newdata->setPrevious($data);
                $newdata->setApproved(false);
                $newdata->setApprovedBy(null);
                $this->em->persist($newdata);
                $this->em->persist($data);
                $this->em->flush();

                $status['status'] = true;
                $status['snack'] = 'Freigabe entfernt und neue Version angelegt';
                $status['data'] = $newdata->getId();
                $status['clone'] = true;
            } else {
                $data->setApproved(true);
                $data->setApprovedBy($user);
                $status['status'] = true;
                $status['snack'] = 'Element freigegeben';
                $this->em->persist($data);
                $this->em->flush();
            }

        } catch (\Exception $e) {
            $status['status'] = false;
            $status['snack'] = 'FEHLER: Element konnte nicht freigegeben werden. Versuchen Sie es noch einmal.';
        }

        return $status;
    }
}
