<?php
namespace binaro\NachschreibarbeitenBundle;

use binaro\NachschreibarbeitenBundle\DependencyInjection\IServNachschreibarbeitenExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IServNachschreibarbeitenBundle extends Bundle
{
    public function getContainerExtension()
    {
        // Manually register the extension to overcome naming issue
        return new IServNachschreibarbeitenExtension();
    }
}

