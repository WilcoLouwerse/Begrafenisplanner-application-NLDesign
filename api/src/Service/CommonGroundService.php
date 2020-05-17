<?php

// src/Service/HuwelijkService.php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CommonGroundService
{
    private $params;
    private $cache;
    private $session;
    private $headers;
    private $requestStack;
    private $flash;
    private $translator;

    public function __construct(ParameterBagInterface $params, SessionInterface $session, CacheInterface $cache, RequestStack $requestStack, FlashBagInterface $flash, TranslatorInterface $translator)
    {
        $this->params = $params;
        $this->session = $session;
        $this->cache = $cache;
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->flash = $flash;
        $this->translator = $translator;

        // To work with NLX we need a couple of default headers
        $this->headers = [
            'Accept'         => 'application/ld+json',
            'Content-Type'   => 'application/json',
            'Authorization'  => $this->params->get('app_commonground_key'),
            // NLX
            'X-NLX-Request-Application-Id' => $this->params->get('app_commonground_id'), // the id of the application performing the request
            // NL Api Strategie
            'Accept-Crs'   => 'EPSG:4326',
            'Content-Crs'  => 'EPSG:4326',
        ];

        if ($session->get('user')) {
            $headers['X-NLX-Request-User-Id'] = $session->get('user')['@id'];
        }

        if ($session->get('process')) {
            $headers[] = $session->get('process')['@id'];
        }

        // We might want to overwrite the guzle config, so we declare it as a separate array that we can then later adjust, merge or otherwise influence
        $this->guzzleConfig = [
            // Base URI is used with relative requests
            'http_errors' => false,
            //'base_uri' => 'https://wrc.zaakonline.nl/applications/536bfb73-63a5-4719-b535-d835607b88b2/',
            // You can set any number of default request options.
            'timeout'  => 4000.0,
            // To work with NLX we need a couple of default headers
            'headers' => $this->headers,
        ];

        // Lets start up a default client
        $this->client = new Client($this->guzzleConfig);
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function getResourceList($url, $query = [], $force = false, $async = false, $autowire = true)
    {
        if(is_array($url) && array_key_exists('component', $url)){
            $component = $this->getComponent($url['component']);
        }
        else {
            $component = false;
        }

        $url = $this->cleanUrl($url, false, $autowire);

        /* This is broken
         $elementList = [];
         foreach($query as $element){
         if(!is_array($element)){
         break;
         }
         $elementList[] = implode("=",$element);
         }
         $elementList = implode(",", $elementList);


         if($elementList){
         $headers['X-NLX-Request-Data-Elements'] = $elementList;
         $headers['X-NLX-Request-Data-Subject'] = $elementList;
         }
         */

        $item = $this->cache->getItem('commonground_'.md5($url));
        if ($item->isHit() && !$force && $this->params->get('app_cache')) {
            // return $item->get();
        }

        // To work with NLX we need a couple of default headers
        $auth = false;
        $headers = $this->headers;

        // Component specific congiguration
        if($component && array_key_exists('accept', $component)){
            $headers['Accept'] = $component['accept'];
        }
        if($component && array_key_exists('auth', $component)){
            switch ($component['auth']) {
                case "jwt":
                    $headers['Authorization'] = 'Bearer '.$this->getJwtToken($component['id'], $component['secret']);
                    break;
                case "username-password":
                    $auth = [$component['username'], $component['password']];
            }
        }

        if (!$async) {
            $response = $this->client->request('GET', $url, [
                'query'   => $query,
                'headers' => $headers,
                'auth' => $auth,
            ]);
        } else {
            $response = $this->client->requestAsync('GET', $url, [
                'query'   => $query,
                'headers' => $headers,
                'auth' => $auth,
            ]);
        }

        $statusCode = $response->getStatusCode();
        $response = json_decode($response->getBody(), true);

        // The trick here is that if statements are executed left to right. So the prosses errors wil only be called when all other conditions are met
        /* @todo 201 hier vewijderen is een hack */
        if ($statusCode != 200  && $statusCode != 201 && !$this->proccesErrors($response, $statusCode, $headers, null, $url, 'GET')) {
            return false;
        }

        $parsedUrl = parse_url($url);

        /* @todo this should look to al @id keus not just the main root */
        $response = $this->convertAtId($response, $parsedUrl);

        // plain json catch
        if (array_key_exists('results', $response)) {
            foreach ($response['results'] as $key => $value) {
                $response['results'][$key] = $this->enrichObject($value, $parsedUrl);
            }
        }

        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cache->save($item);

        return $response;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function getResource($url, $query = [], $force = false, $async = false, $autowire = true)
    {
        if(is_array($url) && array_key_exists('component', $url)){
            $component = $this->getComponent($url['component']);
        }
        else {
            $component = false;
        }

        $url = $this->cleanUrl($url, false, $autowire);

        $item = $this->cache->getItem('commonground_'.md5($url));

        if ($item->isHit() && !$force && $this->params->get('app_cache')) {
            return $item->get();
        }

        // To work with NLX we need a couple of default headers
        $auth = false;
        $headers = $this->headers;
        $headers['X-NLX-Request-Subject-Identifier'] = $url;

        // Component specific congiguration
        if($component && array_key_exists('accept', $component)){
            $headers['Accept'] = $component['accept'];
        }
        if($component && array_key_exists('auth', $component)){
            switch ($component['auth']) {
                case "jwt":
                    $headers['Authorization'] = 'Bearer '.$this->getJwtToken($component['id'], $component['secret']);
                    break;
                case "username-password":
                    $auth = [$component['username'], $component['password']];
            }
        }


        if (!$async) {
            $response = $this->client->request('GET', $url, [
                'query'   => $query,
                'headers' => $headers,
                'auth' => $auth,
            ]);
        } else {
            $response = $this->client->requestAsync('GET', $url, [
                'query'   => $query,
                'headers' => $headers,
                'auth' => $auth,
            ]);
        }

        $statusCode = $response->getStatusCode();
        $response = json_decode($response->getBody(), true);

        // The trick here is that if statements are executed left to right. So the prosses errors wil only be called when all other conditions are met
        if ($statusCode != 200 &&  !$this->proccesErrors($response, $statusCode, $headers, null, $url, 'GET')) {
            return false;
        }

        $parsedUrl = parse_url($url);

        $response = $this->convertAtId($response, $parsedUrl);

        $response = $this->enrichObject($response, $parsedUrl);

        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cache->save($item);

        return $response;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function updateResource($resource, $url = null, $async = false, $autowire = true)
    {
        if(is_array($url) && array_key_exists('component', $url)){
            $component = $this->getComponent($url['component']);
        }
        else {
            $component = false;
        }

        $url = $this->cleanUrl($url, $resource, $autowire);

        // To work with NLX we need a couple of default headers
        $auth = false;
        $headers = $this->headers;
        $headers['X-NLX-Request-Subject-Identifier'] = $url;

        // Component specific congiguration
        if($component && array_key_exists('accept', $component)){
            $headers['Accept'] = $component['accept'];
        }
        if($component && array_key_exists('auth', $component)){
            switch ($component['auth']) {
                case "jwt":
                    $headers['Authorization'] = 'Bearer '.$this->getJwtToken($component['id'], $component['secret']);
                    break;
                case "username-password":
                    $auth = [$component['username'], $component['password']];
            }
        }

        $resource = $this->cleanResource($resource);

        foreach ($resource as $key=>$value) {
            if ($value == null || (is_array($value && empty($value)))) {
                unset($resource[$key]);
            }
        }

        if (!$async) {
            $response = $this->client->request('PUT', $url, [
                'body'    => json_encode($resource),
                'headers' => $headers,
                'auth' => $auth,
            ]);
        } else {
            $response = $this->client->requestAsync('PUT', $url, [
                'body'    => json_encode($resource),
                'headers' => $headers,
                'auth' => $auth,
            ]);
        }

        $statusCode = $response->getStatusCode();
        $response = json_decode($response->getBody(), true);

        // The trick here is that if statements are executed left to right. So the prosses errors wil only be called when all other conditions are met
        if ($statusCode != 200 && !$this->proccesErrors($response, $statusCode, $headers, $resource, $url, 'PUT')) {
            return false;
        }

        $parsedUrl = parse_url($url);

        $response = $this->convertAtId($response, $parsedUrl);

        $response = $this->enrichObject($response, $parsedUrl);

        // Lets cache this item for speed purposes
        $item = $this->cache->getItem('commonground_'.md5($url));
        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cache->save($item);

        return $response;
    }

    /*
     * Create a sresource on a common ground component
     */
    public function createResource($resource, $url = null, $async = false, $autowire = true)
    {
        if(is_array($url) && array_key_exists('component', $url)){
            $component = $this->getComponent($url['component']);
        }
        else {
            $component = false;
        }

        $url = $this->cleanUrl($url, $resource, $autowire);

        // Set headers
        $auth = false;
        $headers = $this->headers;

        // Component specific congiguration
        if($component && array_key_exists('accept', $component)){
            $headers['Accept'] = $component['accept'];
        }
        if($component && array_key_exists('auth', $component)){
            switch ($component['auth']) {
                case "jwt":
                    $headers['Authorization'] = 'Bearer '.$this->getJwtToken($component['id'], $component['secret']);
                    break;
                case "username-password":
                    $auth = [$component['username'], $component['password']];
            }
        }

        $resource = $this->cleanResource($resource);

        if (!$async) {
            $response = $this->client->request('POST', $url, [
                'body'    => json_encode($resource),
                'headers' => $headers,
                'auth' => $auth,
            ]);
        } else {
            $response = $this->client->requestAsync('POST', $url, [
                'body'    => json_encode($resource),
                'headers' => $headers,
                'auth' => $auth,
            ]);
        }

        $statusCode = $response->getStatusCode();
        $response = json_decode($response->getBody(), true);

        // The trick here is that if statements are executed left to right. So the prosses errors wil only be called when all other conditions are met
        if ($statusCode != 201 && $statusCode != 200 && !$this->proccesErrors($response, $statusCode, $headers, $resource, $url, 'POST')) {
            return false;
        }

        $parsedUrl = parse_url($url);

        $response = $this->convertAtId($response, $parsedUrl);

        $response = $this->enrichObject($response, $parsedUrl);

        // Lets cache this item for speed purposes
        $item = $this->cache->getItem('commonground_'.md5($url.'/'.$response['id']));
        $item->set($response);
        $item->expiresAt(new \DateTime('tomorrow'));
        $this->cache->save($item);

        return $response;
    }

    /*
     * Delete a single resource from a common ground component
     */
    public function deleteResource($resource, $url = null, $async = false, $autowire = true)
    {
        if(is_array($url) && array_key_exists('component', $url)){
            $component = $this->getComponent($url['component']);
        }
        else {
            $component = false;
        }

        $url = $this->cleanUrl($url, $resource, $autowire);

        // Set headers
        $auth = false;
        $headers = $this->headers;

        // Component specific congiguration
        if($component && array_key_exists('accept', $component)){
            $headers['Accept'] = $component['accept'];
        }
        if($component && array_key_exists('auth', $component)){
            switch ($component['auth']) {
                case "jwt":
                    $headers['Authorization'] = 'Bearer '.$this->getJwtToken($component['id'], $component['secret']);
                    break;
                case "username-password":
                    $auth = [$component['username'], $component['password']];
            }
        }

        if (!$async) {
            $response = $this->client->request('DELETE', $url, [
                'headers' => $headers,
                'auth' => $auth,
            ]);
        } else {
            $response = $this->client->requestAsync('DELETE', $url, [
                'headers' => $headers,
                'auth' => $auth,
            ]);
        }

        $statusCode = $response->getStatusCode();
        $response = json_decode($response->getBody(), true);

        // The trick here is that if statements are executed left to right. So the prosses errors wil only be called when all other conditions are met
        if ($statusCode != 204 && !$this->proccesErrors($response, $statusCode, $headers, $resource, $url, 'DELETE')) {
            return false;
        }

        // Remove the item from cache
        $this->cache->delete('commonground_'.md5($url));

        return true;
    }

    /*
     * The save fucntion should only be used by applications that can render flashes
     */
    public function saveResource($resource, $endpoint = false, $autowire = true)
    {
        $endpoint = $this->cleanUrl($endpoint, $resource, $autowire);

        // @tododit zijn echt te veel ifjes

        // If the resource exists we are going to update it, if not we are going to create it
        if (array_key_exists('@id', $resource) && $resource['@id']) {
            if ($this->updateResource($resource, null, false, $autowire)) {
                // Lets renew the resource
                $resource = $this->getResource($resource['@id'], [], false, false, $autowire);
                if (array_key_exists('name', $resource)) {
                    $this->flash->add('success', $resource['name'].' '.$this->translator->trans('saved'));
                } elseif (array_key_exists('reference', $resource)) {
                    $this->flash->add('success', $resource['reference'].' '.$this->translator->trans('saved'));
                } elseif (array_key_exists('id', $resource))  {
                    $this->flash->add('success', $resource['id'].' '.$this->translator->trans('saved'));
                } else{
                    $this->flash->add('success', $this->translator->trans('saved'));
                }
            } else {
                if (array_key_exists('name', $resource)) {
                    $this->flash->add('error', $resource['name'].' '.$this->translator->trans('could not be saved'));
                } elseif (array_key_exists('reference', $resource)) {
                    $this->flash->add('error', $resource['reference'].' '.$this->translator->trans('could not be saved'));
                } elseif (array_key_exists('id', $resource)) {
                    $this->flash->add('error', $resource['id'].' '.$this->translator->trans('could not be saved'));
                } else{
                    $this->flash->add('error', $this->translator->trans('could not be saved'));
                }
            }
        } else {
            if ($createdResource = $this->createResource($resource, $endpoint, false, $autowire)) {
                // Lets renew the resource
                $resource = $this->getResource($createdResource['@id'], [], false, false, $autowire);
                $this->flash->add('success', $resource['name'].' '.$this->translator->trans('created'));
            } else {
                if (array_key_exists('name', $resource)) {
                    $this->flash->add('error', $resource['name'].' '.$this->translator->trans('could not be created'));
                } elseif (array_key_exists('reference', $resource)) {
                    $this->flash->add('error', $resource['reference'].' '.$this->translator->trans('could not be created'));
                } elseif (array_key_exists('id', $resource)) {
                    $this->flash->add('error', $resource['id'].' '.$this->translator->trans('could not be created'));
                } else{
                    $this->flash->add('error', $this->translator->trans('could not be created'));
                }
            }
        }

        return $resource;
    }

    /*
     * Get the current application from the wrc
     */
    public function getApplication($force = false, $async = false)
    {
        $applications = $this->getResourceList('https://wrc.'.$this->getDomain().'/applications', ['domain'=>$this->getDomain()], $force, $async);

        if (count($applications['hydra:member']) > 0) {
            return $applications['hydra:member'][0];
        }

        return false;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function clearFromsCash($resource, $url = false)
    {
        $url = $this->cleanUrl($url, $resource);

        $this->cache->delete('commonground_'.md5($url));
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function cleanResource($resource)
    {
        unset($resource['@context']);
        unset($resource['@id']);
        unset($resource['@type']);
        unset($resource['id']);
        unset($resource['_links']);
        unset($resource['_embedded']);

        return $resource;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function proccesErrors($response, $statusCode, $headers, $resource, $url, $proces)
    {
        // Non-Json suppor

        if (!$response) {
            $this->flash->add('error', $statusCode.':'.$url);
        }
        // ZGW support
        elseif (!array_key_exists('@type', $response) && array_key_exists('types', $response)) {
            $this->flash->add('error', $this->translator->trans($response['detail']));
        }
        // Hydra Support
        elseif (array_key_exists('@type', $response) && $response['@type'] == 'ConstraintViolationList') {
            foreach ($response['violations'] as $violation) {
                $this->flash->add('error', $violation['propertyPath'].' '.$this->translator->trans($violation['message']));
            }

            return false;
        } else {
            throw new HttpException($statusCode, $url.' returned: '.json_encode($response));
        }

        return $response;
    }

    /*
     * Turns plain json objects into ld+jsons
     */
    private function enrichObject(array $object, array $parsedUrl)
    {
        while (!array_key_exists('@id', $object)) {
            if (array_key_exists('url', $object)) {
                $object['@id'] = $object['url'];
                break;
            }

            // Lets see if the path ends in a UUID
            /*
    		$path_parts = pathinfo($parsedUrl["path"]);
    		$path_parts['dirname'];

    		if (is_string($path_parts['dirname']) && (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $path_parts['dirname']) == 1)) {
    			$object['@id'] = implode($parsedUrl);
    			break;
    		}
    		*/
            break;
        }

        //while(!array_key_exists ('@type', $object)){
        //
        //}

        while (!array_key_exists('@self', $object)) {
            if (array_key_exists('@id', $object)) {
                $object['@self'] = $object['@id'];
                break;
            }
            if (array_key_exists('url', $object)) {
                $object['@self'] = $object['url'];
                break;
            }

            break;
        }

        while (!array_key_exists('id', $object)) {
            // Lets see if an UUID is provided
            if (array_key_exists('uuid', $object)) {
                $object['id'] = $object['uuid'];
                break;
            }

            // What if we dont have an id at all?
            if (!array_key_exists('@id', $object)) {
                break;
            }

            // Lets see if the path ends in a UUID
            $parsedId = parse_url($object['@id']);

            $path_parts = pathinfo($parsedId['path']);
            $path_parts['dirname'];

            //var_dump($path_parts);

            if (is_string($path_parts['basename']) && (preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $path_parts['basename']) == 1)) {
                $object['id'] = $path_parts['basename'];
                break;
            }
            //$object['id']=$path_parts['basename'];

            break;
        }

        while (!array_key_exists('name', $object)) {
            // ZGW specifiek
            if (array_key_exists('omschrijving', $object)) {
                $object['name'] = $object['omschrijving'];
                break;
            }

            // Fallbask set de id als naams
            $object['name'] = $object['id'];
            break;
        }

        while (!array_key_exists('dateCreated', $object)) {
            // ZGW specifiek
            if (array_key_exists('registratiedatum', $object)) {
                $object['dateCreated'] = $object['registratiedatum'];
                break;
            }

            break;
        }

        /*
    	while(!array_key_exists ('dateModified', $object)){

    		break;
    	}
    	*/
        return $object;
    }

    /*
     * Finds @id keys and replaceses the relative link with an absolute link
     */
    private function convertAtId(array $object, array $parsedUrl)
    {
        if (array_key_exists('@id', $object)) {
            $object['@id'] = $parsedUrl['scheme'].'://'.$parsedUrl['host'].$object['@id'];
        }
        foreach ($object as $key=>$subObject) {
            if (is_array($subObject)) {
                $object[$key] = $this->convertAtId($subObject, $parsedUrl);
            }
        }

        return $object;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function cleanUrl($url = false, $resource = false, $autowire = true)
    {
        // The Url might be an array of component information
        if( is_array ($url) && array_key_exists ('component' , $url ) &&  $component = $this->getComponent($url['component']) ){
            $route = '';
            if( array_key_exists ('type' , $url )){
                $route = $route.'/'.$url['type'];
            }
            if( array_key_exists ('id' , $url )){
                $route = $route.'/'.$url['id'];
            }

            $url = $component['location'].$route;

            // Components may overule the autowire
            if(key_exists("autowire",$component)){
                $autowire = $component["autowire"];
            }


        }

        if (!$url && $resource && array_key_exists('@id', $resource)) {
            $url = $resource['@id'];
        }

        // Split enviroments, if the env is not dev the we need add the env to the url name
        $parsedUrl = parse_url($url);

        // We only do this on non-production enviroments
        if ($this->params->get('app_env') != 'prod' && $autowire) {

            // Lets make sure we dont have doubles
            $url = str_replace($this->params->get('app_env').'.', '', $url);

            // e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
            $host = explode('.', $parsedUrl['host']);
            $subdomain = $host[0];
            $url = str_replace($subdomain.'.', $subdomain.'.'.$this->params->get('app_env').'.', $url);
        }

        // Remove trailing slash
        $url = rtrim($url, '/');

        return $url;
    }

    /*
     * Header overrides for ZGW and Camunda
     */
    public function setCredentials($username, $password)
    {
        $this->headers['auth'] = [$username, $password];
    }

    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /*
     * Get a single resource from a common ground componant
     */
    public function getDomain()
    {
        $request = $this->requestStack->getCurrentRequest();
        $host = $request->getHost();

        if ($host == '' | $host == 'localhost') {
            $host = $this->params->get('app_domain');
        }

        $host_names = explode('.', $host);
        $host = $host_names[count($host_names) - 2].'.'.$host_names[count($host_names) - 1];

        return $host;
    }

    /*
     * Get a list of available commonground components
     */
    public function getComponent(string $code)
    {
        // Create the list
        $components = $this->params->get('common_ground.components');

        // Get the component
        if(array_key_exists ($code , $components)){
            return $components[$code];
        }

        // Lets default to a negative
        return false;
    }

    /*
     * Get a list of available commonground components
     */
    public function getComponentList()
    {
        $components = [
            'cc'  => ['href'=>'http://cc.zaakonline.nl',  'authorization'=>''],
            'lc'  => ['href'=>'http://lc.zaakonline.nl',  'authorization'=>''],
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

        $item = $this->cache->getItem('componentHealth_'.md5($component));
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
        $this->cache->save($item);

        return $component;
    }

    /*
     * Get a list of available resources on a commonground componant
     */
    public function getComponentResources(string $component, $force = false)
    {
        $componentList = $this->getComponentList();

        $item = $this->cache->getItem('componentResources_'.md5($component));
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
        $this->cache->save($item);

        return $component;
    }

    /*
     * Get the current application from the wrc
     */
    public function getJwtToken($clientId, $secret)
    {

        $userId = '';
        $userRepresentation = '';

        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256', 'client_identifier' => $clientId]);

        // Create token payload as a JSON string
        $payload = json_encode(['iss' => $clientId, 'client_id' =>$clientId, 'user_id' => $userId, 'user_representation' => $userRepresentation, 'iat' => time()]);

        // Encode Header to Base64Url String
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Create Signature Hash
        $signature = hash_hmac('sha256', $base64UrlHeader.'.'.$base64UrlPayload, $secret, true);

        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Return JWT
        return $base64UrlHeader.'.'.$base64UrlPayload.'.'.$base64UrlSignature;
    }
}
