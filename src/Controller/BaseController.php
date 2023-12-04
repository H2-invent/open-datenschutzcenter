<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected array $defaultParams = [];

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

    protected function setBackButton(string $url): void
    {
        $this->defaultParams['backButton'] = $url;
    }

    protected function getDefaultParams(): array
    {
        return \array_merge($this->defaultParams, [
            // add some defaults here
        ]);
    }

    protected function renderView(string $view, array $parameters = []): string
    {
        return parent::renderView($view, \array_merge($parameters, $this->getDefaultParams()));
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        return parent::render($view, \array_merge($parameters, $this->getDefaultParams()), $response);
    }
}