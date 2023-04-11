<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\Type\UploadTyp;
use App\Service\CurrentTeamService;
use App\Service\ParserService;
use App\Service\SecurityService;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    #[Route(path: '/upload', name: 'upload_new')]
    public function index(Request $request, FilesystemInterface $internFileSystem, ParserService $parserService, SecurityService $securityService, CurrentTeamService $currentTeamService)
    {
        $user = $this->getUser();
        $team = $currentTeamService->getCurrentAdminTeam($user);
        // Admin Route only
        if (!$securityService->adminCheck($this->getUser(), $team)) {
            return $this->redirectToRoute('dashboard');
        }

        $upload = new Upload();
        $form = $this->createForm(UploadTyp::class, $upload);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $upload->setUpdatedAt(new \DateTime());
            $upload->setUId('Not completed');
            $upload->setAmount(0);
            $upload = $form->getData();
            $em->persist($upload);
            if (!preg_match('/odif$/', $upload->getFile())) {
                return $this->redirectToRoute('upload_fail', array('message' => '
                Der Dateityp ist fehlerhaft. Die Datei muss mit .odif enden.'));
            }

            $em->flush();
            $stream = $internFileSystem->read($upload->getFile());
            $data = json_decode($stream);
            $verify = $parserService->verify($data);
            if($verify != 1){
                $internFileSystem->delete($upload->getFile());
                return $this->redirectToRoute('upload_fail',array('message'=>'
                Die Signatur ist ungültig. Bitte kontaktieren Sie die Personen, die Ihnen die Datei überlassen hat.'));
            }
            $res = false;
            switch ($data->table) {
                case 'Audit':
                    $res = $parserService->parseAudit($data, $team, $this->getUser(), $upload);
                    break;
                case 'VVT':
                    $res = $parserService->parseVVT($data, $team, $this->getUser(), $upload);
                    break;
                default:
                    break;
            }

            if ($res) {
                $internFileSystem->delete($upload->getFile());
                return $this->redirectToRoute('upload_success');
            } else {
                $internFileSystem->delete($upload->getFile());
                return $this->redirectToRoute('upload_fail',array('message'=>'
                Die Datei ist fehlerhaft und kann nicht eingelesen werden. 
                Es können jedoch bereits Daten in Ihren Datenstamm eingetragen worden sein. 
                Bitte kontaktieren Sie die Personen, die Ihnen die Datei überlassen hat.'));
            }
        }
        return $this->render('upload/new.html.twig', array('form' => $form->createView()));
    }

    #[Route(path: '/upload/success', name: 'upload_success')]
    public function success(Request $request)
    {
        return $this->render('upload/success.html.twig');
    }

    #[Route(path: '/upload/fail', name: 'upload_fail')]
    public function fail(Request $request)
    {
        return $this->render('upload/fail.html.twig',array('message'=>$request->get('message')));
    }
}
