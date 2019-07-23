<?php
// Conduction/CommonGroundBundle/Service/KadasterService.php

/*
 * This file is part of the Conduction Common Ground Bundle
 *
 * (c) Conduction <info@conduction.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use GuzzleHttp\Client ;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;

class KadasterService
{
	private $config;
	private $params;
	private $client;
	private $commonGroundService;
	
	public function __construct(ParameterBagInterface $params, CacheInterface $cache)
	{
		$this->params = $params;
		$this->cash = $cache;
		
		$this->client= new Client([
				// Base URI is used with relative requests
				'base_uri' => $this->params->get('common_ground.bag.location'),
				// You can set any number of default request options.
				'timeout'  => 4000.0,
				// This api key needs to go into params 
				'headers' => ['X-Api-Key' => $this->params->get('common_ground.bag.apikey')]
		]);
	}
	
	public function setConfig($config)
	{
		$this->config= $config;
	}
	
	public function getNummeraanduidingen($query)
	{
		$response = $this->client->request('GET','nummeraanduidingen',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
	
	public function getNummeraanduiding($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('nummeraanduiding_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','nummeraanduidingen/'.$id);
		$response = json_decode($response->getBody(), true);
		
		$item->set($response);
		$item->expiresAt(new \DateTime('tomorrow'));
		$this->cash->save($item);
		
		return $item->get();
	}
	
	public function getWoonplaatsens($query)
	{
		$response = $this->client->request('GET','woonplaatsen',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
	
	public function getWoonplaats($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('woonplaats_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','woonplaatsen/'.$id);
		$response = json_decode($response->getBody(), true);
		
		// Save to cash
		$item->set($response);
		$item->expiresAt(new \DateTime('January 1st Next Year')); // By law dutch localities only change in Januray the first
		$this->cash->save($item);
		
		return $item->get();
	}
	
	public function getOpenbareruimtes($query)
	{
		// Lets first try the cach
		$response = $this->client->request('GET','openbare-ruimtes',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
	
	public function getOpenbareruimte($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('openbare-ruimte_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','openbare-ruimtes/'.$id);
		$response = json_decode($response->getBody(), true);
		
		// Save to cach
		$item->set($response);
		$item->expiresAfter(3600);
		$this->cash->save($item);
		
		return $item->get();
	}
		
	public function getPanden($query)
	{
		$response = $this->client->request('GET','panden',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
	
	public function getPand($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('pand_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','panden/'.$id);
		$response = json_decode($response->getBody(), true);
		
		// Save to cach
		$item->set($response);
		$item->expiresAfter(3600);
		$this->cash->save($item);
		
		return $item->get();
	}
	
	public function getVerblijfsobjecten($query)
	{
		$response = $this->client->request('GET','verblijfsobjecten',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
	
	public function getVerblijfsobject($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('verblijfsobject_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','verblijfsobjecten/'.$id);
		$response = json_decode($response->getBody(), true);
		
		// Save to cach
		$item->set($response);
		$item->expiresAfter(3600);
		$this->cash->save($item);
		
		return $item->get();
	}
	
	public function getLigplaatsen($query)
	{
		$response = $this->client->request('GET','ligplaatsen',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
		
	public function getLigplaats($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('ligplaats_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','ligplaatsen/'.$id);
		$response = json_decode($response->getBody(), true);
		
		// Save to cach
		$item->set($response);
		$item->expiresAfter(3600);
		$this->cash->save($item);
		
		return $item->get();
	}
	
	public function getStandplaatsen($query)
	{
		$response = $this->client->request('GET','standplaatsen',[
				'query' => $query
		]);
		$response = json_decode($response->getBody(), true);
		return $response['_embedded'];
	}
	
	public function getStandplaats($id)
	{
		// Lets first try the cach
		$item = $this->cash->getItem('standplaats_'.md5($id));
		if ($item->isHit()) {
			return $item->get();
		}
		
		$response = $this->client->request('GET','standplaatsen/'.$id);
		$response = json_decode($response->getBody(), true);
		
		// Save to cach
		$item->set($response);
		$item->expiresAfter(3600);
		$this->cash->save($item);
		
		return $item->get();
	}
		
	//
	public function analyseUri($uri)
	{
		// Add trycatch
		$url= parse_url($uri); 
		
		// Lets see if we can get an id
		$pathparts = explode("/",$url["path"]); 
		// We should check for a valid uuid and if so return an separate ID and endpoint
		//if(){
			// The id should be te last path part
			$url['id'] = end($pathparts);
			// The endpoint should be the path bart before the last part
			$url['endpoint'] = prev($pathparts);
		//}
			return $url;
	}
	
	// Somedoc block here
	public function getAdresOnHuisnummerPostcode($huisnummer, $postcode)
	{
		// Lets start with th getting of nummer aanduidingen
		$now = New \Datetime();
		$query=["huisnummer"=>$huisnummer, "postcode"=>$postcode, "geldigOp"=>$now->format('Y-m-d')];
		$nummeraanduidingen = $this->getNummeraanduidingen($query);
		
		// Lets setup an responce
		$responces = [];
		// Then we need to enrich that
		foreach($nummeraanduidingen['nummeraanduidingen'] as $nummeraanduiding){
					
			$adresseerbaarObject = $this->analyseUri($nummeraanduiding["_links"]['adresseerbaarObject']['href']);
			
			// Let see what we got here in terms of object
			switch ($adresseerbaarObject['endpoint']) {
				case 'verblijfsobjecten':
					$nummeraanduiding['adres'] = $this->getVerblijfsobject($adresseerbaarObject['id']);
					$responce['type'] = 'verblijfsobject';
					$responce['oppervlakte'] = $nummeraanduiding['adres']['oppervlakte'];
					break;
				case 'ligplaatsen':
					$nummeraanduiding['adres'] = $this->getLigplaats($adresseerbaarObject['id']);
					$responce['type'] = 'ligplaats';
					break;
				case 'standplaatsen':
					$nummeraanduiding['adres'] = $this->getStandplaats($adresseerbaarObject['id']);
					$responce['type'] = 'standplaats';
					break;
			}			
			$responce['status'] = $nummeraanduiding['adres']['status'];
			//var_dump ($nummeraanduiding);
			
			// Lets copy the following information if it exsists
			if(array_key_exists('huisnummer', $nummeraanduiding)){$responce['huisnummer'] = $nummeraanduiding['huisnummer'];}
			if(array_key_exists('huisnummertoevoeging', $nummeraanduiding)){$responce['huisnummertoevoeging'] = $nummeraanduiding['huisnummertoevoeging'];}
			if(array_key_exists('postcode', $nummeraanduiding)){$responce['postcode']= $nummeraanduiding['postcode'];}
			
			// Then the apropriote openbare ruimte
			$bijbehorendeOpenbareRuimte = $this->analyseUri($nummeraanduiding['_links']['bijbehorendeOpenbareRuimte']['href']);
			$nummeraanduiding['openbareRuimte'] = $this->getOpenbareruimte($bijbehorendeOpenbareRuimte['id']);
			$responce['straat'] = $nummeraanduiding['openbareRuimte']['naam'];
			
			// Then the gemeente
			$bijbehorendeOpenbareRuimte = $this->analyseUri($nummeraanduiding['openbareRuimte']['_links']['bijbehorendeWoonplaats']['href']);
			$nummeraanduiding['woonplaats'] = $this->getWoonplaats($bijbehorendeOpenbareRuimte['id']);	
			
			$responce['woonplaats'] = $nummeraanduiding['woonplaats']['naam'];
			
			// Dan willen we nog wat links toevoegen
			$responce['_links'] = [];
			$responce['_links']['nummeraanduiding'] = $nummeraanduiding['_links']['self'];
			$responce['_links']['bijbehorendeOpenbareRuimte'] = $nummeraanduiding['_links']['bijbehorendeOpenbareRuimte'];
			$responce['_links']['bijbehorendeWoonplaats'] = $nummeraanduiding['openbareRuimte']['_links']['bijbehorendeWoonplaats'];
			$responce['_links']['adresseerbaarObject'] = $nummeraanduiding["_links"]['adresseerbaarObject'];
			
			// Lets add the current responce to the array of responces
			$responces[] = $responce;
		}
		
		return $responces;
	}
	
}
