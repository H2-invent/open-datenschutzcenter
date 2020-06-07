<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use App\Entity\AuditTom;
use App\Entity\AuditTomStatus;
use App\Entity\AuditTomZiele;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTGrundlage;
use App\Entity\VVTPersonen;
use App\Entity\VVTRisiken;
use App\Entity\VVTStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ParserService
{
    private $em;
    private $parameterBag;
    public function __construct( EntityManagerInterface $entityManager,ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->parameterBag = $parameterBag;
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
    function parseVVT($data, $team, User $user)
    {
        //try {
            foreach ($data->entry as $e){
                $vvt = new VVT();
                $vvt->setName($e->name);
                $vvt->setZweck($e->zweck);
                $vvt->setLoeschfrist($e->loeschfrist);
                $status = $this->em->getRepository(VVTStatus::class)->findOneBy(array('name'=>$e->status));
                if(!$status){
                    $status = new VVTStatus();
                    $status->setName($e->status);
                    $this->em->persist($status);
                    $this->em->flush();
                }
                $vvt->setStatus($status);
                foreach ($e->grundlage as $g){
                    $grundlage = $this->em->getRepository(VVTGrundlage::class)->findOneBy(array('name'=>$g));
                    if (!$grundlage){
                        $grundlage = new VVTGrundlage();
                        $grundlage->setName($g);
                        $this->em->persist($grundlage);
                        $this->em->flush();
                    }
                    $vvt->addGrundlage($grundlage);
                }
                foreach ($e->personen as $p){
                    $personen = $this->em->getRepository(VVTPersonen::class)->findOneBy(array('name'=>$p));
                    if (!$personen){
                        $personen = new VVTPersonen();
                        $personen->setName($p);
                        $this->em->persist($personen);
                        $this->em->flush();
                    }
                    $vvt->addPersonengruppen($personen);
                }
                foreach ($e->datenCategorie as $d){
                    $datenCategorie = $this->em->getRepository(VVTDatenkategorie::class)->findOneBy(array('name'=>$d));
                    if (!$datenCategorie){
                        $datenCategorie = new VVTDatenkategorie();
                        $datenCategorie->setName($d);
                        $this->em->persist($datenCategorie);
                        $this->em->flush();
                    }
                    $vvt->addKategorien($datenCategorie);
                }
                foreach ($e->risiko as $r){
                    $risiko = $this->em->getRepository(VVTRisiken::class)->findOneBy(array('name'=>$r));
                    if (!$risiko){
                        $risiko = new VVTRisiken();
                        $risiko->setName($r);
                        $this->em->persist($risiko);
                        $this->em->flush();
                    }
                    $vvt->addRisiko($risiko);
                }
                $vvt->setBeurteilungEintritt($e->beurteilungEintritt);
                $vvt->setBeurteilungSchaden($e->beurteilungSchaden);
                $vvt->setInformationspflicht($e->informationspflicht);

                $vvt->setActiv(true);
                $vvt->setCreatedAt(new \DateTime());
                $vvt->setTeam($team);
                $vvt->setUser($user);
                $vvt->setSpeicherung($e->speicherung);
                $vvt->setUserContract($user);
                $vvt->setNummer('VVT-'. hexdec( uniqid() ));
                $this->em->persist($vvt);
            }
            $this->em->flush();
            return true;
       // }catch (\Exception $e){
       //     return false;
       // }
    }

    function verify($data,$signature){
        try {
            $res = openssl_verify($data,hex2bin($signature),file_get_contents($this->parameterBag->get('projectRoot').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'public.key'),OPENSSL_ALGO_SHA256);
            return $res;
        }catch (\Exception $e){
            return 0;
        }

    }
}
