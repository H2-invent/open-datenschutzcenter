<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */
namespace App\Controller;

use App\Entity\AuditTom;
use App\Entity\AuditTomAbteilung;
use App\Entity\AuditTomStatus;
use App\Entity\AuditTomZiele;
use App\Entity\Tom;
use App\Form\Type\AuditTomType;
use App\Form\Type\TomType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TomController extends AbstractController
{
    /**
     * @Route("/tom", name="tom")
     */
    public function index()
    {
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findActivByTeam($this->getUser()->getTeam());
        return $this->render('tom/index.html.twig', [
            'tom' => $tom,
        ]);
    }

    /**
     * @Route("/tom/new", name="tom_new")
     */
    public function addAuditTom(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('fos_user_security_logout');
        }

        $today = new \DateTime();
        $tom = new Tom();
        $tom->setTeam($team);
        $tom->setActiv(true);
        $tom->setCreatedAt($today);
        $tom->setUser($this->getUser());


        $form = $this->createForm(TomType::class, $tom);
        $form->handleRequest($request);

        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $errors = $validator->validate($data);
            if (count($errors) == 0) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($data);
                $em->flush();
                return $this->redirectToRoute('tom');
            }
        }
        return $this->render('tom/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'TOM erstellen',
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => true
        ]);
    }

    /**
     * @Route("/tom/edit", name="tom_edit")
     */
    public function EditTom(ValidatorInterface $validator, Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }
        $today = new \DateTime();
        $tom = $this->getDoctrine()->getRepository(Tom::class)->find($request->get('tom'));

        //Sicherheitsfunktion, dass nur eigene und Default TOMs bearbeitet werden können
        if ($tom->getTeam() !== $team) {
            return $this->redirectToRoute('tom');
        }

        $newTom = clone $tom;
        $newTom->setPrevious($tom);
        $newTom->setCreatedAt($today);
        $newTom->setUser($this->getUser());
        $newTom->setTeam($team);
        $form = $this->createForm(TomType::class, $newTom);
        $form->remove('titel');
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {
            $tom->setActiv(false);
            $newTom = $form->getData();
            $errors = $validator->validate($newTom);
            if (count($errors) == 0) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($newTom);
                $em->persist($tom);
                $em->flush();
                return $this->redirectToRoute('tom');
            }
        }
        return $this->render('tom/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'TOM bearbeiten',
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => false
        ]);
    }

    /**
     * @Route("/tom/clone", name="tom_clone")
     */
    public function CloneTom(Request $request)
    {
        $team = $this->getUser()->getTeam();
        if ($team === null) {
            return $this->redirectToRoute('dashboard');
        }
        $today = new \DateTime();
        $audit = $this->getDoctrine()->getRepository(AuditTom::class)->findAllByTeam(1);

        $em = $this->getDoctrine()->getManager();

        foreach ($audit as $data) {
            if ($data->getCreatedAt() > $team->getClonedAt()) {
                $newAudit = clone $data;
                $newAudit->setTeam($team);
                $newAudit->setCreatedAt($today);
                $em->persist($newAudit);
            }

        }

        //set ClonedAt Date to be able to update later newer versions
        $team->setclonedAt($today);

        $em->persist($team);
        $em->flush();

        return $this->redirectToRoute('audit_tom');

    }
}
