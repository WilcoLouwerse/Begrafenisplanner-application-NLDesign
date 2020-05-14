<?php


namespace App\Service;

use Conduction\CommonGroundBundle\Service\CommonGroundService;

class ApiService
{
    private $commonGroundService;
    public function __construct(CommonGroundService $commonGroundService){
        $this->commonGroundService = $commonGroundService;
    }
    public function getResourceList($component, $type){
        //TODO rechten
        return $this->commonGroundService->getResourceList(['component'=>$component, 'type'=>$type]);
    }
    public function createResource($resource, $component, $type){
        //TODO rechten
        return $this->commonGroundService->createResource($resource, ['component'=>$component, 'type'=>$type]);
    }
    public function getResource($component, $type, $id){
        //TODO rechten
        return $this->commonGroundService->getResource(['component'=>$component, 'type'=>$type, 'id'=>$id]);
    }
    public function updateResource($resource, $component, $type, $id){
        //TODO rechten
        return $this->commonGroundService->updateResource($resource, ['component'=>$component, 'type'=>$type, 'id'=>$id]);
    }
    public function deleteResource($component, $type, $id){
        //TODO rechten
        return $this->commonGroundService->deleteResource(null, ['component'=>$component, 'type'=>$type, 'id'=>$id]);
    }
}
