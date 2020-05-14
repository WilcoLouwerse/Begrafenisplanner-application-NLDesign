<?php

namespace App\Controller;

use App\Service\ApiService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * The API controller handles all requests that should be redirected to a component for the benefit of AJAX Calls
 *
 * Class ApiController
 * @package App\Controller
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }
    /**
     * @Route("/{component}/{type}")
     */
    public function collectionAction($component, $type, Request $request, ApiService $apiService){
        if($request->isMethod('POST')){
            return $apiService->createResource(json_decode($request->getContent(),true), $component, $type);
        }
        elseif($request->isMethod('GET')){
            return $apiService->getResourceList($component, $type);
        }
        else{
            throw HttpError::METHOD_NOT_ALLOWED;
        }
    }
    /**
     * @Route("/{component}/{type}/{id}")
     */
    public function itemAction($component, $type, $id, Request $request, ApiService $apiService){
        if($request->isMethod('PUT')){
            return $apiService->updateResource(json_decode($request->getContent(),true), $component, $type, $id);
        }
        elseif($request->isMethod('GET')){
            return $apiService->getResource($component, $type, $id);
        }
        elseif($request->isMethod('DELETE')){
            return $apiService->deleteResource($component, $type, $id);
        }
        else{
            throw HttpError::METHOD_NOT_ALLOWED;
        }
    }

}
