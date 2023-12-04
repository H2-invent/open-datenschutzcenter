<?php

namespace App\Controller;

//use App\Entity\Settings;
//use App\Repository\SettingsRepository;
use App\Entity\Settings;
use App\Form\Type\SettingsType;
use App\Repository\SettingsRepository;
use App\Service\SecurityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SettingsController extends BaseController
{
    #[Route(path: '/manage_settings', name: 'manage_settings')]
    public function manage(
        ValidatorInterface     $validator,
        EntityManagerInterface $em,
        Request                $request,
        SettingsRepository     $settingsRepository,
        SecurityService        $securityService
    ): Response
    {
        $user = $this->getUser();

        if (!$securityService->superAdminCheck($user)) {
            return $this->redirectToRoute('dashboard');
        }

        $settings = $settingsRepository->findOne();

        if (!$settings) {
            $settings = new Settings();
        }

        $form = $this->createForm(SettingsType::class, $settings);
        $form->handleRequest($request);

        $errors = array();

        if ($form->isSubmitted() && $form->isValid()) {
            $newSettings = $form->getData();
            $settings->setUseKeycloakGroups($newSettings->getUseKeycloakGroups());
            $errors = $validator->validate($settings);
            if (count($errors) == 0) {
                $em->persist($settings);
                $em->flush();
            }
        }

        return $this->render('settings/settings.html.twig', [
            'form' => $form->createView(),
            'errors' => $errors,
            'settings' => $settings
        ]);
    }
}
