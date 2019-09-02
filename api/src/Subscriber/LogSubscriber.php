<?php

namespace App\Subscriber;

use ApiPlatform\Core\Exception\InvalidArgumentException;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

use App\Service\RequestTypeService;

class LogSubscriber implements EventSubscriberInterface
{
	private $params;
	private $em;
	private $serializer;
	private $annotationReader;
	
	public function __construct(ParameterBagInterface $params, EntityManagerInterface $em, SerializerInterface $serializer, Reader $annotationReader)
	{
		$this->params = $params;
		$this->em= $em;
		$this->serializer= $serializer;
		$this->annotationReader = $annotationReader;
	}
	
	public static function getSubscribedEvents()
	{
		return [
				KernelEvents::VIEW => ['Log', EventPriorities::PRE_SERIALIZE],
		];
		
	}
	
	public function Log(GetResponseForControllerResultEvent $event)
	{
		$result = $event->getControllerResult();
		$showLogs= $event->getRequest()->query->get('showLogs');
		
		// Lets see if this class has a Loggableannotation
		$loggable = false;
		$reflClass = new \ReflectionClass($result); 
		$annotations = $this->annotationReader->getClassAnnotations($reflClass);
		
		foreach($annotations as $annotation ){
			if(get_class($annotation) == "Gedmo\Mapping\Annotation\Loggable"){
				$loggable = true;
			}
		}
					
		// Only do somthing if we are on te log route and the entity is logable
		/* @todo we should trhow errors here foruser feedback */
		if (!$showLogs || !$loggable) {
			return $result;
		}
				
		$repo = $this->em->getRepository('Gedmo\Loggable\Entity\LogEntry'); // we use default log entry class
		$logs = $repo->getLogEntries($result);
	    
	    // now we need to overide the normal subscriber
		$json = $this->serializer->serialize(
			$logs,
			'jsonhal',['enable_max_depth' => true]
		);
		
		$response = new Response(
				$json,
				Response::HTTP_OK,
				['content-type' => 'application/json+hal']
				);
		
		$event->setResponse($response);
		
		return;
	}	
}
