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
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApproveService
{
    private $em;

    public function __construct(
        EntityManagerInterface      $entityManager,
        private TranslatorInterface $translator,
    )
    {
        $this->em = $entityManager;
    }

    public function approve($data, User $user): array
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
                $status['snack'] = $this->translator->trans(id: 'element.unpublished', domain: 'service');
                $status['data'] = $newdata->getId();
                $status['clone'] = true;
            } else {
                $data->setApproved(true);
                $data->setApprovedBy($user);
                $status['status'] = true;
                $status['snack'] = $this->translator->trans(id: 'element.published', domain: 'service');
                $this->em->persist($data);
                $this->em->flush();
            }

        } catch (Exception $e) {
            $status['status'] = false;
            $status['snack'] = $this->translator->trans(id: 'element.error');
        }

        return $status;
    }
}
