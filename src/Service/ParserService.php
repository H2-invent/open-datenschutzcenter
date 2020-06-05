<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTom;
use App\Entity\AuditTomStatus;
use App\Entity\AuditTomZiele;
use App\Entity\Team;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;


class ParserService
{
    private $em;

    public function __construct( EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }


    function parseTom($data, Team $team,User $user)
    {
       try {
            foreach ($data->entry as $e){
                $tom = new AuditTom();
                $tom->setFrage($e->frage);
                $tom->setBemerkung($e->bemerkung);
                $tom->setEmpfehlung($e->empfehlung);
                $tom->setKategorie($e->categorie);
                $status = $this->em->getRepository(AuditTomStatus::class)->findOneBy(array('name'=>$e->status));
                if(!$status){
                    $status = new AuditTomStatus();
                    $status->setName($e->status);
                    $this->em->persist($status);
                    $this->em->flush();
                }
                $tom->setStatus($status);
                foreach ($e->ziel as $z){
                   $ziel = $this->em->getRepository(AuditTomZiele::class)->findOneBy(array('name'=>$z));
                   if (!$ziel){
                       $ziel = new AuditTomZiele();
                       $ziel->setName($z);
                       $ziel->setTeam($team);
                       $ziel->setActiv(true);
                       $this->em->persist($ziel);
                       $this->em->flush();
                   }
                    $tom->addZiele($ziel);
                }
                $tom->setActiv(true);
                $tom->setCreatedAt(new \DateTime());
                $tom->setTeam($team);
                $tom->setUser($user);
                $tom->setNummer('AUDIT-'. hexdec( uniqid() ));
                $this->em->persist($tom);
            }
            $this->em->flush();
            return true;
        }catch (\Exception $e){
            return false;
       }

    }
    function parseVVT($data)
    {
        return true;
    }
}
