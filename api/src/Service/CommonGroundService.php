<?php

// src/Service/HuwelijkService.php

namespace App\Service;

use GuzzleHttp\Client;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
		$this->cash = $cache;
		$this->session= $session;
		$this->requestStack = $requestStack;
		$this->flash = $flash;
		$this->translator = $translator;
		
		// To work with NLX we need a couple of default headers
		$this->headers = [
				'Accept'        => 'application/ld+json',
				'Content-Type'  => 'application/json',
				'Authorization'  => $this->params->get('app_commonground_key'),
				'X-NLX-Request-Application-Id' => $this->params->get('app_commonground_id')// the id of the application performing the request
		];
		
		if($session->get('user')){
			$headers['X-NLX-Request-User-Id'] = $session->get('user')['@id'];
		}
		
		if($session->get('process')){
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
	public function getResourceList($url, $query = [], $force = false, $async = false)
	{
		if (!$url) {
			return false;
		}
		
		// Split enviroments, if the env is not dev the we need add the env to the url name
		$parsedUrl = parse_url($url);
		
		// We only do this on non-production enviroments
		if($this->params->get('app_env') != "prod"){
			
			// Lets make sure we dont have doubles
			$url = str_replace($this->params->get('app_env').'.','',$url);
			
			// e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
			$host = explode('.', $parsedUrl['host']);
			$subdomain = $host[0];
			$url = str_replace($subdomain.'.',$subdomain.'.'.$this->params->get('app_env').'.',$url);
		}
		
		// To work with NLX we need a couple of default headers
		$headers = $this->headers;
		
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
		
		$item = $this->cash->getItem('commonground_'.md5($url));
		if ($item->isHit() && !$force) {
			//return $item->get();
		}
		
		if(!$async){
			$response = $this->client->request('GET', $url, [
					'query' => $query,
					'headers' => $headers,
			]);
		}
		else {
			
			$response = $this->client->requestAsync('GET', $url, [
					'query' => $query,
					'headers' => $headers,
			]);
		}
		
		if($response->getStatusCode() != 200){
			var_dump('GET returned:'.$response->getStatusCode());
			var_dump(json_encode($query));
			var_dump(json_encode($headers));
			var_dump(json_encode($url));
			var_dump($response->getBody());
			var_dump(json_decode($response->getBody(), true));
			die;
		}
		
		$response = json_decode($response->getBody(), true);
		
		/* @todo this should look to al @id keus not just the main root */
		if(array_key_exists('hydra:member', $response) && $response['hydra:member']){
			foreach($response['hydra:member'] as $key => $embedded){
				// Set the id
				if(array_key_exists('@id', $embedded) && $embedded['@id']){
					$response['hydra:member'][$key]['@id'] =  $parsedUrl["scheme"]."://".$parsedUrl["host"].$embedded['@id'];
				}
				
				// To prevent unnececary calls we cash al the items we get
				$item = $this->cash->getItem('commonground_'.md5($response['hydra:member'][$key]['@id'] ));
				$item->set($embedded);
				$item->expiresAt(new \DateTime('tomorrow'));
				$this->cash->save($item);
			}
		}
		
		$item->set($response);
		$item->expiresAt(new \DateTime('tomorrow'));
		$this->cash->save($item);
		
		return $response;
	}
	
	/*
	 * Get a single resource from a common ground componant
	 */
	public function getResource($url, $query = [], $force = false, $async = false)
	{
		
		if (!$url) {
			//return false;
		}
		
		// Split enviroments, if the env is not dev the we need add the env to the url name
		$parsedUrl = parse_url($url);
		
		// We only do this on non-production enviroments
		if($this->params->get('app_env') != "prod"){
			
			// Lets make sure we dont have doubles
			$url = str_replace($this->params->get('app_env').'.','',$url);
			
			// e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
			$host = explode('.', $parsedUrl['host']);
			$subdomain = $host[0];
			$url = str_replace($subdomain.'.',$subdomain.'.'.$this->params->get('app_env').'.',$url);
		}
		
		$item = $this->cash->getItem('commonground_'.md5($url));
		
		if ($item->isHit() && !$force) {
			return $item->get();
		}
		
		// To work with NLX we need a couple of default headers
		$headers = $this->headers;
		$headers['X-NLX-Request-Subject-Identifier'] = $url;
		
		if(!$async){
			$response = $this->client->request('GET', $url, [
					'query' => $query,
					'headers' => $headers,
			]);
		}
		else {
			
			$response = $this->client->requestAsync('GET', $url, [
					'query' => $query,
					'headers' => $headers,
			]);
		}
		
		if($response->getStatusCode() != 200){
			var_dump('GET returned:'.$response->getStatusCode());
			var_dump(json_encode($query));
			var_dump(json_encode($headers));
			var_dump(json_encode($url));
			var_dump($response->getBody());
			die;
		}
		
		$response = json_decode($response->getBody(), true);
		
		if(array_key_exists('@id', $response) && $response['@id']){
			$response['@id'] = $parsedUrl["scheme"]."://".$parsedUrl["host"].$response['@id'];
		}
		
		$item->set($response);
		$item->expiresAt(new \DateTime('tomorrow'));
		$this->cash->save($item);
		
		return $response;
	}
	
	/*
	 * Get a single resource from a common ground componant
	 */
	public function updateResource($resource, $url = null, $async = false)
	{
		if (!$url && array_key_exists('@id', $resource)) {
			$url = $resource['@id'];
		}
		
		// Split enviroments, if the env is not dev the we need add the env to the url name
		$parsedUrl = parse_url($url);
		
		// We only do this on non-production enviroments
		if($this->params->get('app_env') != "prod"){
			
			// Lets make sure we dont have doubles
			$url = str_replace($this->params->get('app_env').'.','',$url);
			
			// e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
			$host = explode('.', $parsedUrl['host']);
			$subdomain = $host[0];
			$url = str_replace($subdomain.'.',$subdomain.'.'.$this->params->get('app_env').'.',$url);
		}
		
		// To work with NLX we need a couple of default headers
		$headers = $this->headers;
		$headers['X-NLX-Request-Subject-Identifier'] = $url;
		
		unset($resource['@context']);
		unset($resource['@id']);
		unset($resource['@type']);
		unset($resource['id']);
		unset($resource['_links']);
		unset($resource['_embedded']);
		
		foreach($resource as $key=>$value){
			if($value == null || (is_array($value && empty($value)))){
				unset($resource[$key]);
			}
		}
		
		
		if(!$async){
			$response = $this->client->request('PUT', $url, [
					'body' => json_encode($resource),
					'headers' => $headers,
			]);
		}
		else {
			
			$response = $this->client->requestAsync('PUT', $url, [
					'body' => json_encode($resource),
					'headers' => $headers,
			]);
		}
		
		$statusCode= $response->getStatusCode();
		$response = json_decode($response->getBody(), true);
		
		if($response->getStatusCode() != 200){
			//Should be cases
			if($response['@type'] == 'ConstraintViolationList'){
				foreach($response['violations'] as $violation){
					$this->flash->add('error', $violation['propertyPath'].' '.$this->translator->trans($violation['message']));
				}
				
				return false;
			}
			else{
				var_dump('POST returned:'.$statusCode);
				var_dump($headers);
				var_dump(json_encode($resource));
				var_dump(json_encode($url));
				var_dump($response);
				die;
			}
		}
		
		if(array_key_exists('@id', $response) && $response['@id']){
			$response['@id'] = $parsedUrl["scheme"]."://".$parsedUrl["host"].$response['@id'];
		}
		
		// Lets cash this item for speed purposes
		$item = $this->cash->getItem('commonground_'.md5($url));
		$item->set($response);
		$item->expiresAt(new \DateTime('tomorrow'));
		$this->cash->save($item);
		
		return $response;
	}
	
	/*
	 * Create a sresource on a common ground component
	 */
	public function createResource($resource, $url = null, $async = false)
	{
		if (!$url && array_key_exists('@id', $resource)) {
			$url = $resource['@id'];
		}
		
		// Split enviroments, if the env is not dev the we need add the env to the url name
		$parsedUrl = parse_url($url);
		
		// We only do this on non-production enviroments
		if($this->params->get('app_env') != "prod"){
			
			// Lets make sure we dont have doubles
			$url = str_replace($this->params->get('app_env').'.','',$url);
			
			// e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
			$host = explode('.', $parsedUrl['host']);
			$subdomain = $host[0];
			$url = str_replace($subdomain.'.',$subdomain.'.'.$this->params->get('app_env').'.',$url);
		}
		
		// Remove trailing slash
		$url = rtrim($url, '/');
		
		// Set headers
		$headers = $this->headers;
		
		unset($resource['@context']);
		unset($resource['@id']);
		unset($resource['@type']);
		unset($resource['id']);
		unset($resource['_links']);
		unset($resource['_embedded']);
		
		if(!$async){
			$response = $this->client->request('POST', $url, [
					'body' => json_encode($resource),
					'headers' => $headers,
			]);
		}
		else {
			$response = $this->client->requestAsync('POST', $url, [
					'body' => json_encode($resource),
					'headers' => $headers,
			]);
		}
		
		
		$statusCode= $response->getStatusCode();
		$response = json_decode($response->getBody(), true);
		
		if($statusCode!= 201 && $statusCode != 200){
			//Should be cases
			if($response['@type'] == 'ConstraintViolationList'){
				foreach($response['violations'] as $violation){
					$this->flash->add('error', $violation['propertyPath'].' '.$this->translator->trans($violation['message']));
				}
				
				return false;
			}
			else{
				var_dump('POST returned:'.$statusCode);
				var_dump($headers);
				var_dump(json_encode($resource));
				var_dump(json_encode($url));
				var_dump($response);
				die;
			}
		}
		
		
		if(array_key_exists('@id', $response) && $response['@id']){
			$response['@id'] = $parsedUrl["scheme"]."://".$parsedUrl["host"].$response['@id'];
		}
		
		// Lets cash this item for speed purposes
		$item = $this->cash->getItem('commonground_'.md5($url.'/'.$response['id']));
		$item->set($response);
		$item->expiresAt(new \DateTime('tomorrow'));
		$this->cash->save($item);
		
		return $response;
	}
	
	
	/*
	 * Delete a single resource from a common ground component
	 */
	public function deleteResource($resource, $url = null, $async = false)
	{
		if (!$url && array_key_exists('@id', $resource)) {
			$url = $resource['@id'];
		}
		
		// Split enviroments, if the env is not dev the we need add the env to the url name
		$parsedUrl = parse_url($url);
		
		// We only do this on non-production enviroments
		if($this->params->get('app_env') != "prod"){
			
			// Lets make sure we dont have doubles
			$url = str_replace($this->params->get('app_env').'.','',$url);
			
			// e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
			$host = explode('.', $parsedUrl['host']);
			$subdomain = $host[0];
			$url = str_replace($subdomain.'.',$subdomain.'.'.$this->params->get('app_env').'.',$url);
		}
		
		// Remove trailing slash
		$url = rtrim($url, '/');
		
		// Set headers
		$headers = $this->headers;
				
		if(!$async){
			$response = $this->client->request('DELETE', $url, [
					'headers' => $headers,
			]);
		}
		else {
			$response = $this->client->requestAsync('DELETE', $url, [
					'headers' => $headers,
			]);
		}
		
		
		$statusCode= $response->getStatusCode();
		$response = json_decode($response->getBody(), true);
		
		if($statusCode!= 201 && $statusCode != 200){
			//Should be cases
			if($response['@type'] == 'ConstraintViolationList'){
				foreach($response['violations'] as $violation){
					$this->flash->add('error', $violation['propertyPath'].' '.$this->translator->trans($violation['message']));
				}
				
				return false;
			}
			else{
				var_dump('POST returned:'.$statusCode);
				var_dump($headers);
				var_dump(json_encode($resource));
				var_dump(json_encode($url));
				var_dump($response);
				die;
			}
		}
		
		// Remove the item from cash		
		$this->cash->delete('commonground_'.md5($url));
		
		return true;
	}
	
	/*
	 * The save fucntion should only be used by applications that can render flashes
	 */
	public function saveResource($resource, $endpoint = false)
	{
		
		// If the resource exists we are going to update it, if not we are going to create it
		if($resource['@id']){
			if($this->updateResource($resource)){
				// Lets renew the resource
				$resource= $this->getResource($resource['@id']);
				$this->flash->add('success', $resource['name'].' '.$this->translator->trans('saved'));
			}
			else{
				$this->flash->add('error', $resource['name'].' '.$this->translator->trans('could not be saved'));
			}
		}
		else{
			if($this->createResource($resource, $endpoint)){
				// Lets renew the resource
				$resource= $this->getResource($resource['@id']);
				$this->flash->add('success', $resource['name'].' '.$this->translator->trans('created'));
			}
			else{
				$this->flash->add('error', $resource['name'].' '.$this->translator->trans('could not be created'));
			}
		}
		
		return $resource;
	}
	
	
	/*
	 * Get the current application from the wrc
	 */
	public function getApplication($force = false, $async = false)
	{
		$applications = $this->getResourceList('https://wrc.'.$this->getDomain().'/applications',["domain"=>$this->getDomain()],$force, $async);
		
		if(count($applications['hydra:member'])>0){
			return $applications['hydra:member'][0];
		}
		
		return false;
	}
	
	/*
	 * Get a single resource from a common ground componant
	 */
	public function clearCash($resource, $url = false)
	{
		if (!$url && array_key_exists('@id', $resource)) {
			$url = $resource['@id'];
		}
		
		// Split enviroments, if the env is not dev the we need add the env to the url name
		$parsedUrl = parse_url($url);
		
		// We only do this on non-production enviroments
		if($this->params->get('app_env') != "prod"){
			
			// Lets make sure we dont have doubles
			$url = str_replace($this->params->get('app_env').'.','',$url);
			
			// e.g https://wrc.larping.eu/ becomes https://wrc.dev.larping.eu/
			$host = explode('.', $parsedUrl['host']);
			$subdomain = $host[0];
			$url = str_replace($subdomain.'.',$subdomain.'.'.$this->params->get('app_env').'.',$url);
		}
		
		// Remove trailing slash
		$url = rtrim($url, '/');
		
		$this->cash->delete('commonground_'.md5($url));
	}
	
	
	/*
	 * Get a single resource from a common ground componant
	 */
	public function getDomain()
	{
		$request = $this->requestStack->getCurrentRequest();
		$host = $request->getHost();
		
		if($host == "" | $host == "localhost"){$host = $this->params->get('app_domain');}
		
		$host_names = explode(".", $host);
		$host = $host_names[count($host_names)-2] . "." . $host_names[count($host_names)-1];
		
		return $host;
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
