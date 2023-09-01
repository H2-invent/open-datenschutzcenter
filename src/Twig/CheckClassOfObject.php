<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function PHPUnit\Framework\isInstanceOf;

class CheckClassOfObject extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('checkClass', [$this, 'checkClass']),
        ];
    }

    public function checkClass($ouT, $value)
    {
        // Check for http at beginning of string
        $classname =get_class($ouT);
        $res = $classname == $value;
        return $res;
    }
}
