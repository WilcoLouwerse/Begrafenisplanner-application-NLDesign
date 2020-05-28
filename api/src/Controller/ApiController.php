<?php

namespace App\Controller;

use App\Service\ApiService;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

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
    public function collectionAction($component, $type, Request $request, ApiService $apiService, SerializerInterface $serializer){
        $contentType = $request->headers->get('accept');
        if (!$contentType) {
            $contentType = $request->headers->get('Accept');
        }
        switch ($contentType) {
            case 'application/json':
                $renderType = 'json';
                break;
            case 'application/ld+json':
                $renderType = 'jsonld';
                break;
            case 'application/hal+json':
                $renderType = 'jsonhal';
                break;
            default:
                $contentType = 'application/json';
                $renderType = 'json';
        }
        if($request->isMethod('POST')){
            $results = $apiService->createResource(json_decode($request->getContent(),true), $component, $type);
        }
        elseif($request->isMethod('GET')){
            $results = $apiService->getResourceList($component, $type);
        }
        else{
            throw new HttpException(405, "METHOD NOT ALLOWED");
        }
        $response = $serializer->serialize(
            $results,
            $renderType,
            ['enable_max_depth'=> true]
        );

        // Creating a response
        $response = new Response(
            $response,
            Response::HTTP_OK,
            ['content-type' => $contentType]
        );
        return $response;
    }
    /**
     * @Route("/{component}/{type}/{id}")
     * @Route("/{component}/{type}/{id}/{log}")
     */
    public function itemAction($component, $type, $id, $log = null, Request $request, ApiService $apiService, SerializerInterface $serializer){
        $contentType = $request->headers->get('accept');
        if (!$contentType) {
            $contentType = $request->headers->get('Accept');
        }
        switch ($contentType) {
            case 'application/json':
                $renderType = 'json';
                break;
            case 'application/ld+json':
                $renderType = 'jsonld';
                break;
            case 'application/hal+json':
                $renderType = 'jsonhal';
                break;
            default:
                $contentType = 'application/json';
                $renderType = 'json';
        }
        if($request->isMethod('PUT')){
            $results = $apiService->updateResource(json_decode($request->getContent(),true), $component, $type, $id);
        }
        elseif($request->isMethod('GET')){
            $results = $apiService->getResource($component, $type, $id, $log);
        }
        elseif($request->isMethod('DELETE')){
            $results = $apiService->deleteResource($component, $type, $id);
        }
        else {
            throw new HttpException(405, "METHOD NOT ALLOWED");
        }

        $response = $serializer->serialize(
            $results,
            $renderType,
            ['enable_max_depth'=> true]
        );

        // Creating a response
        $response = new Response(
            $response,
            Response::HTTP_OK,
            ['content-type' => $contentType]
        );

        return $response;
    }

}
