<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 15.05.2020
 * Time: 09:15
 */
namespace App\Controller;

use App\Entity\AuditTom;
use App\Entity\Tom;
use App\Form\Type\TomType;
use App\Service\SecurityService;
use App\Service\TomService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TomController extends AbstractController
{
    /**
     * @Route("/tom", name="tom")
     */
    public function index(SecurityService $securityService)
    {
        $team = $this->getUser()->getTeam();
        $tom = $this->getDoctrine()->getRepository(Tom::class)->findActivByTeam($team);

        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('dashboard');
        }

        return $this->render('tom/index.html.twig', [
            'tom' => $tom,
        ]);
    }

    /**
     * @Route("/tom/new", name="tom_new")
     */
    public function addAuditTom(ValidatorInterface $validator, Request $request, SecurityService $securityService, TomService $tomService)
    {
        $team = $this->getUser()->getTeam();
        if ($securityService->teamCheck($team) === false) {
            return $this->redirectToRoute('tom');
        }

        $tom = $tomService->newTom($team, $this->getUser());

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
    public function EditTom(ValidatorInterface $validator, Request $request, SecurityService $securityService, TomService $tomService)
    {
        $team = $this->getUser()->getTeam();
        $tom = $this->getDoctrine()->getRepository(Tom::class)->find($request->get('tom'));

        if ($securityService->teamDataCheck($tom, $team) === false) {
            return $this->redirectToRoute('tom');
        }

        $newTom = $tomService->cloneTom($tom, $this->getUser());

        $form = $this->createForm(TomType::class, $newTom);
        $form->remove('titel');
        $form->handleRequest($request);
        $errors = array();
        if ($form->isSubmitted() && $form->isValid()) {

            // Is Current Version already a historical version.
            if ($tom->getActiv() === false) {
                return $this->redirectToRoute('tom_edit', array('id' => $tom->getId(), 'snack' => 'Version ist nicht mehr aktiv und kann nicht geändert werden.'));
            }

            $tom->setActiv(false);
            $newTom = $form->getData();
            $errors = $validator->validate($newTom);
            if (count($errors) == 0) {

                $em = $this->getDoctrine()->getManager();
                $em->persist($newTom);
                $em->persist($tom);
                $em->flush();
                return $this->redirectToRoute('tom_edit', ['tom' => $newTom->getId(), 'snack' => 'Erfolgreich gepeichert']);
            }
        }
        return $this->render('tom/edit.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'title' => 'TOM bearbeiten',
            'tom' => $tom,
            'activ' => $tom->getActiv(),
            'activTitel' => false,
            'snack' => $request->get('snack')
        ]);
    }

    /**
     * @Route("/tom/clone", name="tom_clone")
     */
    public function cloneTom(Request $request)
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
