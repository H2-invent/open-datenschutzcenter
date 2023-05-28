<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\Type\UploadTyp;
use App\Service\CurrentTeamService;
use App\Service\ParserService;
use App\Service\SecurityService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class UploadController extends AbstractController
{


    public function __construct(private readonly TranslatorInterface $translator,
                                private EntityManagerInterface       $em,
    )
    {
    }

    #[Route(path: '/upload/fail', name: 'upload_fail')]
    public function fail(Request $request): Response
    {
        return $this->render('upload/fail.html.twig', array('message' => $request->get('message')));
    }

    #[Route(path: '/upload', name: 'upload_new')]
    public function index(
        Request             $request,
        FilesystemOperator $internFilesystem,
        ParserService       $parserService,
        SecurityService     $securityService,
        CurrentTeamService  $currentTeamService,
    ): Response
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
            $upload->setUpdatedAt(new DateTime());
            $upload->setUId('Not completed');
            $upload->setAmount(0);
            $upload = $form->getData();
            $this->em->persist($upload);
            if (!preg_match('/odif$/', $upload->getFile())) {
                return $this->redirectToRoute(
                    'upload_fail',
                    [
                        'message' => $this->translator->trans(id: 'error.dataType.notOdif', domain: 'general'),
                    ],
                );
            }

            $this->em->flush();
            $stream = $internFilesystem->read($upload->getFile());
            $data = json_decode($stream);
            $verify = $parserService->verify($data);
            if ($verify != 1) {
                $internFilesystem->delete($upload->getFile());
                return $this->redirectToRoute(
                    'upload_fail',
                    [
                        'message' => $this->translator->trans(id: 'error.signature', domain: 'general'),
                    ],
                );
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
                $internFilesystem->delete($upload->getFile());
                return $this->redirectToRoute('upload_success');
            } else {
                $internFilesystem->delete($upload->getFile());
                return $this->redirectToRoute(
                    'upload_fail',
                    [
                        'message' => $this->translator->trans(id: 'error.file', domain: 'general'),
                    ],
                );
            }
        }
        return $this->render('upload/new.html.twig', array('form' => $form->createView()));
    }

    #[Route(path: '/upload/success', name: 'upload_success')]
    public function success(Request $request): Response
    {
        return $this->render('upload/success.html.twig');
    }
}
