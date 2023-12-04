<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{
    protected function addSuccessMessage(string $message): void
    {
        $this->addFlash('success', $message);
    }

    protected function addInfoMessage(string $message): void
    {
        $this->addFlash('success', $message);
    }

    protected function addErrorMessage(string $message): void
    {
        $this->addFlash('error', $message);
    }
}