<?php

// src/Service/HuwelijkService.php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CommonGroundService
{
    private $params;
    private $cache;
    private $session;

    public function __construct(ParameterBagInterface $params, SessionInterface $session, CacheInterface $cache)
    {
        $this->params = $params;
        $this->session = $session;
        $this->cash = $cache;
        $this->session= $session;

        // We might want to overwrite the guzle config, so we declare it as a separate array that we can then later adjust, merge or otherwise influence
        $this->guzzleConfig = [
            // Base URI is used with relative requests
            'http_errors' => false,
            //'base_uri' => 'https://wrc.zaakonline.nl/applications/536bfb73-63a5-4719-b535-d835607b88b2/',
            // You can set any number of default request options.
            'timeout'  => 4000.0,
            // To work with NLX we need a couple of default headers
            'headers' => [
                'Accept'        => 'application/ld+json',
                'Content-Type'  => 'application/json',
                //'X-NLX-Request-User-Id' => '64YsjzZkrWWnK8bUflg8fFC1ojqv5lDn'				// the id of the user performing the request
                //'X-NLX-Request-Application-Id' => '64YsjzZkrWWnK8bUflg8fFC1ojqv5lDn' 		// the id of the application performing the request
                //'X-NLX-Request-Subject-Identifier' => '64YsjzZkrWWnK8bUflg8fFC1ojqv5lDn' 	// an subject identifier for purpose registration (doelbinding)
                //'X-NLX-Request-Process-Id' => '64YsjzZkrWWnK8bUflg8fFC1ojqv5lDn' 			// a process id for purpose registration (doelbinding)
                //'X-NLX-Request-Data-Elements' => '64YsjzZkrWWnK8bUflg8fFC1ojqv5lDn' 		// a list of requested data elements
                //'X-NLX-Request-Data-Subject' => '64YsjzZkrWWnK8bUflg8fFC1ojqv5lDn' 		// a key-value list of data subjects related to this request. e.g. bsn=12345678,kenteken=ab-12-fg
            ],
        ];

        // Lets start up a default client
        $this->client = new Client($this->guzzleConfig);
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function getResourceList($url, $query = [], $force = false)
    {
        if (!$url) {
            return false;
        }

        $item = $this->cash->getItem('commonground_'.md5($url));
        if ($item->isHit() && !$force) {
            //return $item->get();
        }

        $response = $this->client->request('GET', $url, [
            'query' => $query,
        ]
                );
        
        $response = json_decode($response->getBody(), true);

        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cash->save($item);

        return $response;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function getResource($url, $query = [], $force = false)
    {
        if (!$url) {
            //return false;
        }

        $item = $this->cash->getItem('commonground_'.md5($url));
        if ($item->isHit() && !$force) {
            //return $item->get();
        }

        $response = $this->client->request('GET', $url, [
            'query' => $query,
        ]
        );

        $response = json_decode($response->getBody(), true);

        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cash->save($item);

        return $response;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function updateResource($resource, $url = null)
    {
        if (!$url) {
            return false;
        }
        
        unset($resource['@context']);
        unset($resource['@id']);
        unset($resource['@type']);
        unset($resource['id']);
        unset($resource['_links']);
        unset($resource['_embedded']);

        $response = $this->client->request('PUT', $url, [
            'body' => json_encode($resource),
        ]
        		);
        
        if($response->getStatusCode() != 200){
        	var_dump(json_encode($resource));
        	var_dump(json_encode($url));
        	var_dump(json_encode($response->getBody()));
        	die;
        }

        $response = json_decode($response->getBody(), true);

        // Lets cash this item for speed purposes
        $item = $this->cash->getItem('commonground_'.md5($url));
        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cash->save($item);

        return $response;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function createResource($resource, $url = null)
    {
        if (!$url) {
            return false;
        }

        $response = $this->client->request('POST', $url, [
            'body' => json_encode($resource),
        ]
        );

        
        if($response->getStatusCode() != 201){
        	var_dump(json_encode($resource));
        	var_dump(json_encode($url));
        	var_dump($response->getBody());
        	die;
        }
        
        
        $response = json_decode($response->getBody(), true);


        // Lets cash this item for speed purposes
        $item = $this->cash->getItem('commonground_'.md5($url.'/'.$response['id']));
        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cash->save($item);

        return $response;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function clearCash($url)
    {
    }

    /*
     * Get a list of available commonground components
     */
    public function getComponentList()
    {
        $components = [
            'cc'  => ['href'=>'http://cc.zaakonline.nl', 'authorization'=>''],
            'lc'  => ['href'=>'http://lc.zaakonline.nl', 'authorization'=>''],
            'ltc' => ['href'=>'http://ltc.zaakonline.nl', 'authorization'=>''],
            'brp' => ['href'=>'http://brp.zaakonline.nl', 'authorization'=>''],
            'irc' => ['href'=>'http://irc.zaakonline.nl', 'authorization'=>''],
            'ptc' => ['href'=>'http://ptc.zaakonline.nl', 'authorization'=>''],
            'mrc' => ['href'=>'http://mrc.zaakonline.nl', 'authorization'=>''],
            'arc' => ['href'=>'http://arc.zaakonline.nl', 'authorization'=>''],
            'vtc' => ['href'=>'http://vtc.zaakonline.nl', 'authorization'=>''],
            'vrc' => ['href'=>'http://vrc.zaakonline.nl', 'authorization'=>''],
            'pdc' => ['href'=>'http://pdc.zaakonline.nl', 'authorization'=>''],
            'wrc' => ['href'=>'http://wrc.zaakonline.nl', 'authorization'=>''],
            'orc' => ['href'=>'http://orc.zaakonline.nl', 'authorization'=>''],
            'bc'  => ['href'=>'http://orc.zaakonline.nl', 'authorization'=>''],
        ];

        return $components;
    }

    /*
     * Get the health of a commonground componant
     */
    public function getComponentHealth(string $component, $force = false)
    {
        $componentList = $this->getComponentList();

        $item = $this->cash->getItem('componentHealth_'.md5($component));
        if ($item->isHit() && !$force) {
            //return $item->get();
        }

        //@todo trhow symfony error
        if (!array_key_exists($component, $componentList)) {
            return false;
        } else {
            // Lets swap the component for a

            // Then we like to know al the component endpoints
            $component = $this->getComponentResources($component);
        }

        // Lets loop trough the endoints and get health (the self endpoint is included)
        foreach ($component['endpoints'] as $key=>$endpoint) {

            //var_dump($component['endpoints']);
            //var_dump($endpoint);

            $response = $this->client->request('GET', $component['href'].$endpoint['href'], ['Headers' =>['Authorization' => $component['authorization'], 'Accept' => 'application/health+json']]);
            if ($response->getStatusCode() == 200) {
                //$component['endpoints'][$key]['health'] = json_decode($response->getBody(), true);
                $component['endpoints'][$key]['health'] = false;
            }
        }

        $item->set($component);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cash->save($item);

        return $component;
    }

    /*
     * Get a list of available resources on a commonground componant
     */
    public function getComponentResources(string $component, $force = false)
    {
        $componentList = $this->getComponentList();

        $item = $this->cash->getItem('componentResources_'.md5($component));
        if ($item->isHit() && !$force) {
            //return $item->get();
        }

        //@todo trhow symfony error
        if (!array_key_exists($component, $componentList)) {
            return false;
        } else {
            // Lets swap the component for a version that has an endpoint and authorization
            $component = $componentList[$component];
        }

        $response = $this->client->request('GET', $component['href'], ['Headers' =>['Authorization' => $component['authorization'], 'Accept' => 'application/ld+json']]);

        $component['status'] = $response->getStatusCode();
        if ($response->getStatusCode() == 200) {
            $component['endpoints'] = json_decode($response->getBody(), true);
            // Lets pull any json-ld values
            if (array_key_exists('_links', $component['endpoints'])) {
                $component['endpoints'] = $component['endpoints']['_links'];
            }
        } else {
            $component['endpoints'] = [];
        }

        $item->set($component);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cash->save($item);

        return $component;
    }
}
