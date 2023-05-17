<?php
declare(strict_types=1);

namespace App\Form\Type\Template;

use Symfony\Component\Form\AbstractType;

abstract class BaseType extends AbstractType
{
    abstract protected function getDefaultDomain(): string;

    protected function getOptions(
        string $label,
        bool $required = true,
        ?string $domain = null,
        array $additionalOptions = [],
    ): array
    {
        return array_merge(
            [
                'label' => $label,
                'translation_domain' => $domain ?? $this->getDefaultDomain(),
                'required' => $required,
            ],
            $additionalOptions,
        );
    }
}