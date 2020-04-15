<?php

// src/Service/BRPService.php

namespace App\Service;

use GuzzleHttp\Client;
use http\Env\Request;
use http\Message;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use App\Service\CommonGroundService;
use App\Service\ZgwService;
use App\Service\CamundaService;

class RequestService
{
    private $params;
    private $cache;
    private $client;
    private $session;
    private $commonGroundService;
    private $zgwService;
    private $camundaService;
    private $messageService;

    public function __construct(ParameterBagInterface $params, CacheInterface $cache, SessionInterface $session, CommonGroundService $commonGroundService, ZgwService $zgwService, CamundaService $camundaService, MessageService $messageService)
    {
        $this->params = $params;
        $this->cache = $cache;
        $this->session= $session;
        $this->commonGroundService = $commonGroundService;
        $this->zgwService = $zgwService;
        $this->camundaService = $camundaService;
        $this->messageService = $messageService;

    }
    /*
     * Creates a new requested based on a request type
     */
    public function createFromRequestType($requestType, $requestParent = null ,$user = null, $organization= null, $application= null)
    {
    	// If a user has not been provided let try to get one from the session
    	if(!$user){
    		$user = $this->session->get('user');
    	}
    	// If a user has not been provided let try to get one from the session
    	if(!$organization){
    		$organization = $this->session->get('organization');
    	}
    	// If a user has not been provided let try to get one from the session
    	if(!$application){
    		$application= $this->session->get('application');
    	}
    	// If a request type requires a parent request, and a parent request is not specified then don't start the request
    	if(key_exists('parentRequired', $requestType)
            && $requestType['parentRequired']
            && $requestParent == null
        )
        {
            return false;
        }

        $request= [];
        $request['requestType'] = $requestType['@id'];
        $request['organization'] = $organization['@id']; //@TODO: dit moet de organisatie van het requestType worden, maar daar hangen nog legen RSINs in waar het vrc niets mee kan
        $request['status']='incomplete';
        $request['properties']= [];

    	// Juiste startpagina weergeven
    	if(!array_key_exists ("currentStage", $request) && array_key_exists (0, $requestType['stages'])){
    		$request["currentStage"] = $requestType['stages'][0]['slug'];
    	}

    	$request = $this->commonGroundService->createResource($request, $this->commonGroundService->getComponent('vrc')['href'].'/requests'); //HP Specifiek
        if($user){
            $request['submitters'] = [['brp'=>$user['@id']]];
        }

    	// There is an optional case that a request type is a child of an already exsisting one
    	if($requestParent){
    		$requestParent = $this->commonGroundService->getResource($requestParent);
    		$request['parent'] = $requestParent['@id'];

    		// Lets transfer any properties that are both inthe parent and the child request
    		foreach($requestType['properties'] as $property){

                $slug = $property['slug'];

                // We have to find a better way to work with these two slugs, this hardcoded way stands in the way of more configurability
                if($slug == 'getuige'){
                        $slug = 'getuigen';
                }
                elseif($slug == 'partner'){
                        $slug = 'partners';
                }

    			if(key_exists($slug, $requestParent['properties'])){
    				$request['properties'][$slug] = $requestParent['properties'][$slug];
    			}
    		}
            $contact = $requestParent["submitters"][0]['person'];
            $bsn = null;
    	}
    	// If we dont have parent we need to mkae a contact
        else{
            //Maybe we should make contacts more generic
            $contact = ['givenName'=>$user['naam']['voornamen'],'familyName'=>$user['naam']['geslachtsnaam']];
            $contact= $this->commonGroundService->createResource($contact, $this->commonGroundService->getComponent('cc')['href'].'/people')['@id'];
            $bsn = $user['burgerservicenummer'];
        }
        $request["submitters"][0]['person'] = $contact;


    	// Wat doet partners hier?
        if(!key_exists('partners', $request)){

            $assent = [];
            $assent['name'] = 'Instemming '.$requestType['name'];
            $assent['description'] = 'U bent automatisch toegevoegd aan een '.$requestType['name'].' verzoek omdat u deze zelf heeft opgestart';
            $assent['contact'] = $contact;
            $assent['requester'] = $organization['@id'];
            $assent['person'] = $bsn;
            $assent['request'] = $request['@id'];
            $assent['status'] = 'granted';
            $assent = $this->commonGroundService->createResource($assent, $this->commonGroundService->getComponent('irc')['href'].'/assents');

            $request['properties']['partners'][] = $assent['@id'];
            $request['submitters'][0]['assent'] = $assent['@id'];
        }
    	$request = $this->commonGroundService->updateResource($request, $request['@id']);

    	return $request;
    }


    public function unsetPropertyOnSlug($request, $property, $value = null)
    {
        //@TODO: dit abstraheren
        if($property == "getuige"){
            $property = "getuigen";
        }
    	// Lets see if the property exists
    	if(!array_key_exists ($property, $request['properties'])){
    		return $request;
    	}

    	// If the propery is an array then we only want to delete the givven value
    	if(is_array($request['properties'][$property])){

    		$key = array_search($value, $request['properties'][$property]);
    		$deletedValue = $request['properties'][$property][$key];
    		unset ($request['properties'][$property][$key]);

    		// If the array is now empty we want to drop the property
    		if(count($request['properties'][$property]) == 0){
    			unset ($request['properties'][$property]);
    		}
    	}

    	// If else we just drop the property
    	else{
    	    $deletedValue = $request['properties'][$property];
    		unset ($request['properties'][$property]);
    	}
    	if(key_exists('order',$request['properties'])){
    	    $order = $this->commonGroundService->getResource($request['properties']['order']);
    	    foreach($order['items'] as $item){
    	        if($item['offer'] = $deletedValue){
    	            $this->commonGroundService->deleteResource($item['@id']);
                }
            }
        }

    	return $request;

    }

    public function setPropertyOnSlug($request, $requestType, $slug, $value)
    {
    	// Lets get the curent property
    	$typeProperty = false;

    	foreach ($requestType['properties'] as $property){
    		if($property['slug'] == $slug){
    			$typeProperty= $property;
    			break;
    		}
    	}

    	// If this porperty doesn't exsist for this reqoust type we have an issue
    	if(!$typeProperty){
    		return false;
    	}

    	// Let see if we need to do something special
    	if(array_key_exists ('iri',$typeProperty)){
	    	switch ($typeProperty['iri']) {
                case 'irc/assent':

	    			// This is a new assent so we also need to create a contact
	    			if($value == null || !array_key_exists ('@id', $value)) {

	    				$contact = [];
	    				if($value != null && array_key_exists('givenName',$value)){ $contact['givenName']= $value['givenName'];}
	    				if($value != null && array_key_exists('familyName',$value)){ $contact['familyName']= $value['familyName'];}
	    				if($value != null && array_key_exists('email',$value)){
		    				$contact['emails']=[];
		    				$contact['emails'][]=["name"=>"primary","email"=> $value['email']];
	    				}
	    				if($value != null && array_key_exists('telephone',$value)){
		    				$contact['telephones']=[];
		    				$contact['telephones'][]=["name"=>"primary","telephone"=> $value['telephone']];
	    				}
                        if($contact['telephones'][0]['telephone'] == null)
                        {
                            unset($contact['telephones']);
                        }

	    				if(!empty($contact))
	    				    $contact = $this->commonGroundService->createResource($contact, $this->commonGroundService->getComponent('cc')['href'].'/people');

	    				unset($value['givenName']);
	    				unset($value['familyName']);
	    				unset($value['email']);
	    				unset($value['telephone']);

	    				if($value == null)
	    				    $value = [];
	    				$value['name'] = 'Instemming als '.$slug.' bij '.$requestType["name"];
	    				$value['description'] = 'U bent uitgenodigd als '.$slug.' voor het '.$requestType["name"].'-verzoek dat is opgestart door ';
                        if(array_key_exists('partner', $value)){
                            $value['requester'] = $value['partner'];
                        }
                        else{
                            $value['requester'] = $requestType['sourceOrganization']; //@TODO: ook hier een BRP-verwijzing naar de aanvragende partner
                        }
                        $value['request'] = $request['id'];
	    				$value['status'] = 'requested';
	    				if(!empty($contact))
	    				    $value['contact'] = $contact['@id'];
	    				$value = $this->commonGroundService->createResource($value, $this->commonGroundService->getComponent('irc')['href'].'/assents');
                        $template = $this->commonGroundService->getComponent('wrc')['href'].'/templates/e04defee-0bb3-4e5c-b21d-d6deb76bd1bc';
	    				$this->messageService->createMessage($contact, ['assent'=>$value], $template);
	    			}
	    			else{
	    				//$value = $this->commonGroundService->updateResource($value, $value['@id']);
	    			}
	    			$value = $value['@id'];
	    			break;
                case 'pdc/offer':
                    if(!key_exists('order', $request['properties'])){
                        $order = [];
                        $order['name'] = "Huwelijksplanner order";
                        $order['targetOrganization'] = '002220647'; //@TODO: Dit moet nog een WRC verwijzing gaan worden
                        $order['customer'] = $request['submitters'][0]['person'];
                        $order['remark'] = $request['@id'];
                        $order['stage'] = 'cart'; // Deze zou leeg moeten mogen zijn

                        if (!in_array('description',$order) || !$order['description']) {
                            $order['description'] = "Huwelijksplanner Order";
                        }

                        $order = $this->commonGroundService->createResource($order, $this->commonGroundService->getComponent('orc')['href'].'/orders');

                        $request['properties']['order'] = $order['@id'];
                    }
                    $offer = $this->commonGroundService->getResource($value);
                    if(!isset($order)){
                        $orderId = $order = $request['properties']['order'];

                    }
                    else{
                        $orderId = $order['@id'];
                    }
                    $orderItem = [];
                    $orderItem['offer'] = $offer['@id'];
                    $orderItem['name'] = $offer['name'];
                    if(strlen($offer['description'])<255){
                        $orderItem['description'] = $offer['description'];
                    }else{
                        $orderItem['description'] = ''; //@TODO dit moet weer weg
                    }
                    $orderItem['quantity'] = 1;
                    $orderItem['price'] = number_format($offer['price'], 2);
                    $orderItem['priceCurrency'] = $offer['priceCurrency'];
                    //$orderItem['taxPercentage'] = $offer['taxes'][0]['percentage']; // Taxes in orders en invoices moet worden bijgewerkt
                    $orderItem['taxPercentage'] = 0; /*@todo dit moet dus nog worden gefixed */
                    $orderItem['order'] = $orderId;

                    $orderItem = $this->commonGroundService->createResource($orderItem, $this->commonGroundService->getComponent('orc')['href'].'/order_items');
                    // $request['properties']['order']['items'] .= $orderItem;
                    break;
	    			/*
	    		case 'cc/people':
	    			// This is a new assent so we also need to create a contact
	    			if(!array_key_exists ('@id', $value)) {
	    				$value= $this->commonGroundService->createResource($value, );
	    			}
	    			else{
	    				$value= $this->commonGroundService->updateResource($value, $value['@id']);
	    			}
	    			$value ='http://cc.huwelijksplanner.online'.$value['@id'];
	    			break;
	    		case 'pdc/product':
	    			// This is a new assent so we also need to create a contact
	    			if(!array_key_exists ('@id', $value)) {
	    				$value= $this->commonGroundService->createResource($value, 'https://pdc.huwelijksplanner.online/product');
	    			}
	    			else{
	    				$value= $this->commonGroundService->updateResource($value, $value['@id']);
	    			}
	    			$value = $value['@id'];
	    			break;
	    		case 'vrc/request':
	    			break;
	    		case 'orc/order':
	    			// This is a new assent so we also need to create a contact
	    			if(!$value['@id']){
	    				$value= $this->commonGroundService->createResource($value, 'https://orc.huwelijksplanner.online/order');
	    			}
	    			else{
	    				$value= $this->commonGroundService->updateResource($value, $value['@id']);
	    			}
	    			$value = 'http://orc.huwelijksplanner.online'.$value['@id'];
	    			break;
	    			*/
	    	}
    	}

    	// Let procces the value
    	if($typeProperty['type'] == "array"){
    		// Lets make sure that the value is an array
    		if(!array_key_exists($typeProperty['name'],$request['properties']) || !is_array($request['properties'][$typeProperty['name']])){
    			$request['properties'][$typeProperty['name']] = [];
    		}
    		// If the post is also an array then lets merge the two together
    		if(is_array($value)){
    			$request['properties'][$typeProperty['name']] = array_merge($request['properties'][$typeProperty['name']], $value);
    		}
    		else{
    			$request['properties'][$typeProperty['name']][] = $value;
    		}
    	}
    	else{
    		$request['properties'][$typeProperty['name']] = $value;
    	}

    	return $request;
    }

    public function checkRequestType($request, $requestType)
    {
        //echo "<pre>";
        foreach ($requestType['stages'] as $key=>$stage) {

            // Overwrites for omzetten
            // @TODO: Dit mag toch wel wat configurabeler...
            if (
                    (
                    $stage['name'] == 'getuigen' ||
                    $stage['name'] == 'ambtenaar' ||
                    $stage['name'] == 'locatie' ||
                    $stage['name'] == 'extras' ||
                    $stage['name'] == 'plechtigheid' ||
                    $stage['name'] == 'melding')
                    &&
                    array_key_exists('type', $request['properties'])
                    &&
                    $request['properties']['type'] == 'omzetten'
                ) {
                $requestType['stages'][$key]['completed'] = true;
            }
            if (
                    (
                            $stage['name'] == 'getuigen' ||
                            $stage['name'] == 'ambtenaar' ||
                            $stage['name'] == 'locatie' ||
                            $stage['name'] == 'extras' ||
                            $stage['name'] == 'plechtigheid' ||
                            $stage['name'] == 'melding')
                    &&
                    array_key_exists('type', $request['properties'])
                    &&
                    $request['properties']['type'] != 'omzetten'
                    ) {
                $requestType['stages'][$key]['completed'] = false;
            }

            // Lets see is we have a value for this stage in our request and has a value
            if (
                key_exists('properties', $request)
                &&
                array_key_exists($stage['name'], $request['properties'])
                &&
                $request['properties'][$stage['name']] != null
            ) {

                // Let get the validation rules from the request type
//                $arrIt = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($requestType['properties']));

//                foreach ($arrIt as $sub) {
//                    $subArray = $arrIt->getSubIterator();
//
//                    if (array_key_exists('name', $subArray) and $subArray['name'] === $stage['name']) {
//                        $property = iterator_to_array($subArray);
//                        break;
//                    }
//                }


                foreach($requestType['properties'] as $property){
                    if(is_array($property) && key_exists('name', $property) && $property['name'] == $stage['name']){
                        break;
                    }
                }
                // Als we een waarde hebben en het hoefd geen array te zijn
                if ($property['type'] != 'array') {
                    $requestType['stages'][$key]['completed'] = true;
                }
                // als het een array is zonder minimum waarden
                elseif (!array_key_exists('minItems', $property)) {
                    $requestType['stages'][$key]['completed'] = true;
                }
                // als de array een minimum waarde heeft en die waarde wordt gehaald
                elseif (array_key_exists('minItems', $property) && $property['minItems'] && count($request['properties'][$stage['name']]) >= (int) $property['minItems']) {
                    $requestType['stages'][$key]['completed'] = true;
                } else {
                    $requestType['stages'][$key]['completed'] = false;
                }
            }
            else{
                $requestType['stages'][$key]['completed'] = false;
            }
        }

        return $requestType;
    }

    public function updateRequest($request, $url)
    {
        // Lets see if we need to make a case
        if($request['status'] == 'submitted' && (!$request['cases'] || count($request['status']) == 0 )){
            // Lets look at the request type
            $requestType = $this->commonGroundService->getResource($request['requestType']);

            if(array_key_exists('caseType', $requestType) && $requestType['caseType'] && $case = $this->caseFromRequest($request, $requestType['caseType'])){
                // Lets double check if cases is already an array
                if(!is_array( $request['cases'])){
                    $request['cases'] = [];
                }

                $request['cases'][] = $case['@id'];
            }
        }

        return $this->commongroundService->updateResource($request, $url);
    }

    public function caseFromRequest($request, string $caseType)
    {
        $case = [];
        $case['zaaktype'] = $caseType;
        $case['bronorganisatie'] = $this->commonGroundService->getResource($request->getOrganization())->getRsin();;
        $case['verantwoordelijkeOrganisatie'] = $this->commonGroundService->getResource($request->getOrganization())->getRsin();
        $case['omschrijving'] = $request->getName();
        $case['startdatum'] = date('Y-m-d');

        // Dan gaan we dus een zaak aanmaken
        return $this->zgwService->createResource($case, $caseUrl);
    }
}
