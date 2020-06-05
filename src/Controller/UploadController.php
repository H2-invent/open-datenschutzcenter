<?php

namespace App\Controller;

use App\Entity\Upload;
use App\Form\Type\UploadTyp;
use App\Service\ParserService;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    /**
     * @Route("/upload/new", name="upload_new")
     */
    public function index(Request $request, FilesystemInterface $internFileSystem,ParserService $parserService)
    {
        $upload = new Upload();
        $form = $this->createForm(UploadTyp::class, $upload);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $upload->setUpdatedAt(new \DateTime());
            $upload = $form->getData();
            $em->persist($upload);
            $em->flush();
            $stream = $internFileSystem->read($upload->getFile());
            $data = json_decode($stream);
            $res = false;
            switch ($data->table){
                case 'TOM':
                      $res =  $parserService->parseTom($data,$this->getUser()->getTeam(),$this->getUser());
                    break;
                case 'VVT':
                    $res =  $parserService->parseVVT($data);
                    break;
                default:
                    break;
            }

            if($res){
                return $this->redirectToRoute('upload_success');
            }else{
               return $this->redirectToRoute('upload_fail');
            }
        }
        return $this->render('upload/new.html.twig',array('form'=>$form->createView()));
    }
    /**
     * @Route("/upload/success", name="upload_success")
     */
    public function success(Request $request)
    {
        return $this->render('upload/success.html.twig');
    }
    /**
     * @Route("/upload/fail", name="upload_fail")
     */
    public function fail(Request $requeste)
    {
        return $this->render('upload/success.html.twig');
    }
}
