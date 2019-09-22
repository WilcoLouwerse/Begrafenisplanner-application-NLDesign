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
		/*
		$customDefinition = [
				'name' => 'fields',
				'description' => 'Fields to remove of the output',
				'default' => 'id',
				'in' => 'query',
		];
		
		
		// e.g. add a custom parameter
		$docs['paths']['/foos']['get']['parameters'][] = $customDefinition;
		
		// e.g. remove an existing parameter
		$docs['paths']['/foos']['get']['parameters'] = array_values(array_filter($docs['paths']['/foos']['get']['parameters'], function ($param){
			return $param['name'] !== 'bar';
		}));
			
			// Override title
			$docs['info']['title'] = 'My Api Foo';
			
			return $docs;
		*/
		return $docs;
	}
	
	public function supportsNormalization($data, $format = null)
	{
		return $this->decorated->supportsNormalization($data, $format);
	}
}