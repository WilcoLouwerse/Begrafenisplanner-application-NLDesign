<?php

// src/Twig/Commonground.php

namespace App\Twig;

use App\Service\CommonGroundService;
use Twig\Extension\RuntimeExtensionInterface;

class CommongroundRuntime implements RuntimeExtensionInterface
{
    private $commongroundService;

    public function __construct(CommonGroundService $commongroundService)
    {
        $this->commongroundService = $commongroundService;
    }

    public function getResource($resource)
    {
        return $this->commongroundService->getResource($resource);
    }

    public function getResourceList($query)
    {
        return $this->commongroundService->getResourceList($query);
    }

    public function getComponentList()
    {
        return $this->commongroundService->getComponentList();
    }

    public function getComponentHealth($component)
    {
        return $this->commongroundService->getComponentHealth($component);
    }

    public function getComponentResources($component)
    {
        return $this->commongroundService->getComponentResources($component);
    }
}
