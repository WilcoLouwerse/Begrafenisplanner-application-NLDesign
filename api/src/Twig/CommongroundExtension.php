<?php

// src/Twig/CommongroundExtension.php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommongroundExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            // the logic of this filter is now implemented in a different class
            new TwigFunction('commonground_resource_list', [CommongroundRuntime::class, 'getResourceList']),
            new TwigFunction('commonground_resource', [CommongroundRuntime::class, 'getResource']),
            new TwigFunction('commonground_component_list', [CommongroundRuntime::class, 'getComponentList']),
            new TwigFunction('commonground_component_health', [CommongroundRuntime::class, 'getComponentHealth']),
            new TwigFunction('commonground_component_resources', [CommongroundRuntime::class, 'getComponentResources']),
        ];
    }
}
