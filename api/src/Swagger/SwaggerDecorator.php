<?php
// api/src/Swagger/SwaggerDecorator.php

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;

final class SwaggerDecorator implements NormalizerInterface
{
	private $decorated;
	private $params;
	private $cash;
	
	public function __construct(NormalizerInterface $decorated, ParameterBagInterface $params, CacheInterface $cache)
	{
		$this->decorated = $decorated;
		$this->params = $params;
		$this->cash = $cache;
	}
	
	public function normalize($object, $format = null, array $context = [])
	{
		$docs = $this->decorated->normalize($object, $format, $context);
		
		// Lest add an host
		if($this->params->get('common_ground.oas.host')){
			$docs['host']= $this->params->get('common_ground.oas.host');
		}
		
		// Lets set the servers
		if(array_key_exists ('servers',$docs)){$docs['servers']=[];}
		foreach($this->params->get('common_ground.oas.servers') as $key => $value){
			$docs['servers'][$key] = $value;
			
		}
		
		// Lets set the external documentation
		if(array_key_exists ('externalDocs',$docs)){$docs['externalDocs']=[];}
		foreach($this->params->get('common_ground.oas.externalDocs') as $key => $value){
			$docs['externalDocs'][$key] = $value;
			
		}
		
		// Lets add  the commonground codes
		if(array_key_exists ('x-commonground',$docs)){$docs['x-commonground']=[];}
		
		// Lets set the component type
		$docs['x-commonground']['type'] = $this->params->get('common_ground.oas.type');
		
		// Lets set the devolopers
		if(array_key_exists ('developers',$docs['x-commonground'])){$docs['developers']=[];}
		foreach($this->params->get('common_ground.oas.developers') as $key => $value){
			$docs['x-commonground']['developers'][$key] = $value;
			
		}
		
		// Lets set the build checks
		if(array_key_exists ('builds',$docs['x-commonground'])){$docs['builds']=[];}
		foreach($this->params->get('common_ground.oas.builds') as $key => $value){
			$docs['x-commonground']['builds'][$key] = $value;
		}
		
		/*todo a loop within a lopo is butt ugly */
		foreach($docs['paths'] as $path => $calls){
			
			foreach($calls as $method => $call){
				
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'API-Version',
						'description' => 'The version of the API conform [Landelijke API-strategie.](https://geonovum.github.io/KP-APIs/#versioning)',
						'example'=>'1.0.1',
						'in' => 'header',
				];
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-NLX-Request-User-Id',
						'description' => 'The id of the user performing the request',
						'in' => 'header',
				];
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-NLX-Request-Application-Id',
						'description' => 'The id of the application performing the request',
						'in' => 'header',
				];
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-NLX-Request-Subject-Identifier',
						'description' => 'An subject identifier for purpose registration (doelbinding)',
						'in' => 'header',
				];
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-NLX-Request-Process-Id',
						'description' => 'A process id for purpose registration (doelbinding)',
						'in' => 'header',
				];
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-NLX-Request-Data-Elements',
						'description' => 'A list of requested data elements',
						'in' => 'header',
				];
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-NLX-Request-Data-Subject',
						'description' => 'A key-value list of data subjects related to this request. e.g. `bsn=12345678,kenteken=ab-12-fg`',
						'in' => 'header',
				]; ;
				
				// NLX loging headers
				$call['parameters'][] = [
						'name' => 'X-Audit-Clarification',
						'description' => 'A clarification as to why a request has been made  (doelbinding)',
						'in' => 'header',
				];
				
				
				if($method == "GET"){
					// Lets add the extend functionality
					$call['parameters'][] = [
							'name' => 'extend[]',
							'description' => 'An array of nested objects to include in the return object',
							'in' => 'header',
					];
					// Lets add the fields functionality
					$call['parameters'][] = [
							'name' => 'fields[]',
							'description' => 'An array of fields to return in output, wil return all fields is not supplied',
							'in' => 'header',
					];
					// Lets add some time travel
					$call['parameters'][] = [
							'name' => 'validOn',
							'description' => 'Returns object as valid on a given date time',
							'type' => 'string',
							'format' => 'date-time',
							'in' => 'query',
					];
					$call['parameters'][] = [
							'name' => 'validFrom',
							'description' => 'Returns objects valid from a given date time',
							'type' => 'string',
							'format' => 'date-time',
							'in' => 'query',
					];
					$call['parameters'][] = [
							'name' => 'validUntil',
							'description' => 'Returns objects valid until a given date time',
							'type' => 'string',
							'format' => 'date-time',
							'in' => 'query',
					];
					$call['parameters'][] = [
							'name' => 'showLogs',
							'description' => 'Returns a changes made to an resoure',
							'type' => 'boolean',
					];
				}
				
			}
			
			
			
		}
		return $docs;
	}
	
	public function supportsNormalization($data, $format = null)
	{
		return $this->decorated->supportsNormalization($data, $format);
	}
}