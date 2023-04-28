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
use Symfony\Contracts\Translation\TranslatorInterface;


class DisableService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TranslatorInterface    $translator,
    )
    {
    }

    function disable($data, User $user)
    {
        $status = array();

        if ($data->getActiv() === 1) {
            $data->setActiv(2);
            $data->setApprovedBy($user);
            $status['status'] = true;
            $status['snack'] = $this->translator->trans(id: 'deleted', domain: 'general');
        } else {
            $data->setActiv(1);
            $data->setApprovedBy($user);
            $status['status'] = true;
            $status['snack'] = $this->translator->trans(id: 'restored', domain: 'general');
        }
        $this->em->persist($data);
        $this->em->flush();
        return $status;
    }
}
