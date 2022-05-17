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
use App\Entity\Upload;
use App\Entity\User;
use App\Entity\VVT;
use App\Entity\VVTDatenkategorie;
use App\Entity\VVTDsfa;
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

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->parameterBag = $parameterBag;
    }


    function parseAudit($data, Team $team, User $user, Upload $upload)
    {
        try {
            $upload->setAmount($data->amount);
            $upload->setUId($data->uid);
            $this->em->persist($upload);
            foreach ($data->entry as $e) {
                $audit = new AuditTom();
                if ($e->nummer == null) {
                    $audit->setNummer('AUDIT-' . hexdec(uniqid()));
                } else {
                    $audit->setNummer($e->nummer);
                }
                $audit->setFrage($e->frage);
                $audit->setBemerkung($e->bemerkung);
                $audit->setEmpfehlung($e->empfehlung);
                $audit->setKategorie($e->categorie);
                $status = $this->em->getRepository(AuditTomStatus::class)->findOneBy(array('name' => $e->status));
                if (!$status) {
                    $status = new AuditTomStatus();
                    $status->setName($e->status);
                    $this->em->persist($status);
                    $this->em->flush();
                }
                $audit->setStatus($status);
                foreach ($e->ziel as $z) {
                    $ziel = $this->em->getRepository(AuditTomZiele::class)->findOneBy(array('name' => $z));
                    if (!$ziel) {
                        $ziel = new AuditTomZiele();
                        $ziel->setName($z);
                        $ziel->setTeam($team);
                        $ziel->setActiv(true);
                        $this->em->persist($ziel);
                        $this->em->flush();
                    }
                    $audit->addZiele($ziel);
                }
                $audit->setActiv(true);
                $audit->setCreatedAt(new \DateTime());
                $audit->setTeam($team);
                $audit->setUser($user);
                $this->em->persist($audit);
            }
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }

    function parseVVT($data, $team, User $user, Upload $upload)
    {
        try {
            $upload->setAmount($data->amount);
            $upload->setUId($data->uid);
            $this->em->persist($upload);

            foreach ($data->entry as $e) {
                $vvt = new VVT();
                $vvt->setName($e->name);
                $vvt->setZweck($e->zweck);
                $vvt->setLoeschfrist($e->loeschfrist);
                $status = $this->em->getRepository(VVTStatus::class)->findOneBy(array('name' => $e->status));
                if (!$status) {
                    $status = new VVTStatus();
                    $status->setName($e->status);
                    $status->setNetwork(true);
                    $this->em->persist($status);
                    $this->em->flush();
                }
                $vvt->setStatus($status);
                foreach ($e->grundlage as $g) {
                    $grundlage = $this->em->getRepository(VVTGrundlage::class)->findOneBy(array('name' => $g));
                    if (!$grundlage) {
                        $grundlage = new VVTGrundlage();
                        $grundlage->setName($g);
                        $grundlage->setTeam($team);
                        $grundlage->setActiv(true);
                        $this->em->persist($grundlage);
                        $this->em->flush();
                    }
                    $vvt->addGrundlage($grundlage);
                }
                foreach ($e->personen as $p) {
                    $personen = $this->em->getRepository(VVTPersonen::class)->findOneBy(array('name' => $p));
                    if (!$personen) {
                        $personen = new VVTPersonen();
                        $personen->setName($p);
                        $personen->setTeam($team);
                        $personen->setActiv(true);
                        $this->em->persist($personen);
                        $this->em->flush();
                    }
                    $vvt->addPersonengruppen($personen);
                }
                foreach ($e->datenCategorie as $d) {
                    $datenCategorie = $this->em->getRepository(VVTDatenkategorie::class)->findOneBy(array('name' => $d));
                    if (!$datenCategorie) {
                        $datenCategorie = new VVTDatenkategorie();
                        $datenCategorie->setName($d);
                        $datenCategorie->setTeam($team);
                        $datenCategorie->setActiv(true);
                        $this->em->persist($datenCategorie);
                        $this->em->flush();
                    }
                    $vvt->addKategorien($datenCategorie);
                }
                foreach ($e->risiko as $r) {
                    $risiko = $this->em->getRepository(VVTRisiken::class)->findOneBy(array('name' => $r));
                    if (!$risiko) {
                        $risiko = new VVTRisiken();
                        $risiko->setName($r);
                        $risiko->setTeam($team);
                        $risiko->setActiv(true);
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
                $vvt->setDsb($e->dsbKommentar);
                $vvt->setWeitergabe($e->weitergabe);
                $vvt->setTom($e->hinweisTom);
                $vvt->setUserContract($user);
                if ($e->nummer == null) {
                    $vvt->setNummer('VVT-' . hexdec(uniqid()));
                }else {
                    $vvt->setNummer($e->nummer);
                }


                $this->em->persist($vvt);

                if ($e->dsfa == true) {
                    $dsfa = new VVTDsfa();
                    $dsfa->setBeschreibung($e->dsfaData->beschreibung);
                    $dsfa->setRisiko($e->dsfaData->risiko);
                    $dsfa->setAbhilfe($e->dsfaData->abhilfe);
                    $dsfa->setNotwendigkeit($e->dsfaData->notwendigkeit);
                    $dsfa->setStandpunkt($e->dsfaData->standpunkt);
                    $dsfa->setDsb($e->dsfaData->dsbKommentar);
                    $dsfa->setErgebnis($e->dsfaData->ergebnis);
                    $dsfa->setActiv(true);
                    $dsfa->setCreatedAt(new \DateTime());
                    $dsfa->setUser($user);
                    $dsfa->setVvt($vvt);
                    $this->em->persist($dsfa);
                    $this->em->flush();
                }

                $this->em->persist($vvt);
            }
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    function verify($json)
    {
        try {
            $data = json_encode($json->entry);
            $data = $json->entry;
            $data[] = [$json->uid];
            $data[] = [$json->amount];
            $data[] = [$json->author];
            $data[] = [$json->version];
            $data[] = [$json->table];
            $data = json_encode($data);
            $signature = $json->signature;
            $res = openssl_verify($data, hex2bin($signature), file_get_contents($this->parameterBag->get('projectRoot') . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'public.key'), OPENSSL_ALGO_SHA256);
            return $res;
        } catch (\Exception $e) {
            return 0;
        }

    }
}
