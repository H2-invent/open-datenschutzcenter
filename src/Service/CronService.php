<?php
/**
 * Created by PhpStorm.
 * User: andreas.holzmann
 * Date: 06.06.2020
 * Time: 19:01
 */

namespace App\Service;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class CronService
{
    public function __construct(
        private EntityManagerInterface $em,
        FormFactoryInterface           $formBuilder,
        private LoggerInterface        $logger,
        private TranslatorInterface    $translator,
    )
    {
    }

    function check($request)
    {
        $message = false;

        if ($request->get('token') !== $this->getParameter('cronToken')) {
            $message =
                [
                    'error' => true,
                    'hinweis' => $this->translator->trans(id: 'cron.token.invalid', domain: 'service'),
                    'token' => $request->get('token'),
                    'ip' => $request->getClientIp(),
                ];
            $this->logger->error($message['hinweis'], $message);
        }

        if ($this->getParameter('cronIPAdress') !== $request->getClientIp()) {
            $message = [
                'error' => true,
                'hinweis' => $this->translator->trans(id: 'cron.ip.unauthorized', domain: 'service'),
                'ip' => $request->getClientIp(),
            ];
            $this->logger->error($message['hinweis'], $message);
        }

        return $message;
    }
}
