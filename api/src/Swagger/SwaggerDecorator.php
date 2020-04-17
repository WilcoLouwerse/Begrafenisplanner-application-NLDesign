<?php

// api/src/Swagger/SwaggerDecorator.php

namespace App\Swagger;

use Doctrine\Common\Annotations\Reader as AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    private $metadataFactory;
    private $documentationNormalizer;
    private $decorated;
    private $params;
    private $cash;
    private $em;
    private $annotationReader;
    private $camelCaseToSnakeCaseNameConverter;

    public function __construct(
            NormalizerInterface $decorated,
            ParameterBagInterface $params,
            CacheInterface $cache,
            EntityManagerInterface $em,
            AnnotationReader $annotationReader,
            CamelCaseToSnakeCaseNameConverter $camelCaseToSnakeCaseNameConverter
            ) {
        $this->decorated = $decorated;
        $this->params = $params;
        $this->cash = $cache;
        $this->em = $em;
        $this->annotationReader = $annotationReader;
        $this->camelCaseToSnakeCaseNameConverter = $camelCaseToSnakeCaseNameConverter;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        /* The we need to enrich al the entities and add the autoated routes */

        //var_dump($docs);

        // Lets make sure that we have definitions
        if (!array_key_exists('definitions', $docs)) {
            $docs['definitions'] = [];
        }

        // Lets make sure that we have tags
        if (!array_key_exists('tags', $docs)) {
            $docs['tags'] = [];
        }

        // Lets make sure that we have security and JWT-Claims
        if (!array_key_exists('securityDefinitions', $docs)) {
            $docs['securityDefinitions'] = [];
        }

        // Lets add JWT-Oauth
        $docs['securityDefinitions']['JWT-Oauth'] = [
            'type'            => 'oauth2',
            'authorizationUrl'=> 'http://petstore.swagger.io/api/oauth/dialog',
            'flow'            => 'implicit',
            'scopes'          => [], //scopes will be filled later autmaticly
        ];

        $docs['securityDefinitions']['JWT-Token'] = [
            'type'  => 'apiKey',
            'in'    => 'header',       // can be "header", "query" or "cookie"
            'name'  => 'Authorization',  // name of the header, query parameter or cookie
            'scopes'=> [], //scopes will be filled later autmaticly
        ];

        // Lets get al the entities known to doctrine
        $entities = $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();

        $additionalDocs = [];

        // Then we loop trough the entities to find the api platform entities
        foreach ($entities as $entity) {
            //$reflector = new \ReflectionClass($entity);
            $metadata = $this->em->getClassMetadata($entity);
            $reflector = $metadata->getReflectionClass();

            $properties = $metadata->getReflectionProperties();
            $annotations = $this->annotationReader->getClassAnnotations($reflector);

            foreach ($annotations as $annotation) {
                $annotationReflector = new \ReflectionClass($annotation);
                if ($annotationReflector->name == "ApiPlatform\Core\Annotation\ApiResource") {

                    // Lets add the class info to the tag
                    $shortName = $reflector->getShortName();

                    $factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
                    $docblock = $factory->create($reflector->getDocComment());
                    $summary = $docblock->getSummary();
                    $description = $docblock->getDescription()->render();
                    $description = $summary."\n\n".$description;

                    /*
                    if(){
                    	
                    }
                    */
                    
                    $tag = [];
                    $tag['name'] = $shortName;
                    $tag['description'] = $description;

                    $docs['tags'][] = $tag;

                    // And lets add the aditional docs

                    //$additionalEntityDocs = $this->getAdditionalEntityDocs($entity);
                    $entityDocs = $this->getAdditionalEntityDocs($entity);
                    
                    // Only run if we have aditional docs
                    if (array_key_exists('properties', $entityDocs)) {
                        $additionalDocs = array_merge($additionalDocs, $entityDocs['properties']);
                    }

                    // Security
                    $docs['securityDefinitions']['JWT-Oauth']['scopes'] = array_merge($docs['securityDefinitions']['JWT-Oauth']['scopes'], $entityDocs['security']);
                    $docs['securityDefinitions']['JWT-Token']['scopes'] = array_merge($docs['securityDefinitions']['JWT-Token']['scopes'], $entityDocs['security']);

                    break;
                }
            }
        }

        // Ruben: Oke dit is echt but lelijk
        $schemas = (array) $docs['definitions'];
        foreach ($schemas as $schemaName => $schema) {

            // We can only merge if we actually have content
            if (!in_array($schemaName, $additionalDocs)) {
                continue;
            }

            $additionalDocs[$schemaName] = array_merge((array) $schema, $additionalDocs[$schemaName]);

            $properties = (array) $schema['properties'];
            foreach ($properties as $propertyName => $property) {
                $additionalDocs[$schemaName]['properties'][$propertyName] = array_merge((array) $property, $additionalDocs[$schemaName]['properties'][$propertyName]);
            }
        }
        $docs['definitions'] = $additionalDocs;

        // Lest add an host
        if ($this->params->get('common_ground.oas.host')) {
            $docs['host'] = $this->params->get('common_ground.oas.host');
        }

        // Lets set the servers
        if (array_key_exists('servers', $docs)) {
            $docs['servers'] = [];
        }
        foreach ($this->params->get('common_ground.oas.servers') as $key => $value) {
            $docs['servers'][$key] = $value;
        }

        // Lets set the external documentation
        if (array_key_exists('externalDocs', $docs)) {
            $docs['externalDocs'] = [];
        }
        foreach ($this->params->get('common_ground.oas.externalDocs') as $key => $value) {
            $docs['externalDocs'][$key] = $value;
        }

        // Lets add  the commonground codes
        if (array_key_exists('x-commonground', $docs)) {
            $docs['x-commonground'] = [];
        }

        // Lets set the component type
        $docs['x-commonground']['type'] = $this->params->get('common_ground.oas.type');

        // Lets set the devolopers
        if (array_key_exists('developers', $docs['x-commonground'])) {
            $docs['developers'] = [];
        }
        foreach ($this->params->get('common_ground.oas.developers') as $key => $value) {
            $docs['x-commonground']['developers'][$key] = $value;
        }

        // Lets set the build checks
        if (array_key_exists('builds', $docs['x-commonground'])) {
            $docs['builds'] = [];
        }
        foreach ($this->params->get('common_ground.oas.builds') as $key => $value) {
            $docs['x-commonground']['builds'][$key] = $value;
        }

        /*todo a loop within a lopo is butt ugly */
        foreach ($docs['paths'] as $path => $calls) {
            foreach ($calls as $method => $call) {

                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'Authorization',
                    'description' => 'The JWT of the entity performing the request',
                    'in'          => 'header',
                ];
                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'API-Version',
                    'description' => 'The version of the API conform [Landelijke API-strategie.](https://geonovum.github.io/KP-APIs/#versioning)',
                    'example'     => '1.0.1',
                    'in'          => 'header',
                ];
                /*
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
                */
                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'X-NLX-Logrecord-ID',
                    'description' => 'A  globally unique id of the request, which makes a request traceable throughout the network.',
                    'in'          => 'header',
                ];
                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'X-NLX-Request-Process-Id',
                    'description' => 'A process id for purpose registration (doelbinding)',
                    'in'          => 'header',
                ];
                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'X-NLX-Request-Data-Elements',
                    'description' => 'A list of requested data elements',
                    'in'          => 'header',
                ];
                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'X-NLX-Request-Data-Subject',
                    'description' => 'A key-value list of data subjects related to this request. e.g. `bsn=12345678, kenteken=ab-12-fg`',
                    'in'          => 'header',
                ];
                // NLX loging headers
                $call['parameters'][] = [
                    'name'        => 'X-NLX-Audit-Clarification',
                    'description' => 'A clarification as to why a request has been made  (doelbinding)',
                    'in'          => 'header',
                ];

                if ($method == 'get') {

                    // Health JSON
                    $call['produces'][] = 'application/health+json';

                    // WEBSUB header
                    $call['parameters'][] = [
                        'name'        => 'Link',
                        'description' => 'A [websub](https://www.w3.org/TR/websub/#discovery) header like <https://hub.example.com/>; rel="hub"',
                        'in'          => 'header',
                    ];

                    // Lets add the extend functionality
                    $call['parameters'][] = [
                        'name'        => 'extend[]',
                        'required'    => false,
                        'description' => 'An array of nested objects to include in the return object',
                        'in'          => 'query',
                        'schema'      => ['type'=>'array'],
                    ];
                    // Lets add the fields functionality
                    $call['parameters'][] = [
                        'name'        => 'fields[]',
                        'required'    => false,
                        'description' => 'An array of fields to return in output, wil return all fields is not supplied',
                        'in'          => 'query',
                        'schema'      => ['type'=>'array'],
                    ];
                    // Lets add some time travel
                    $call['parameters'][] = [
                        'name'        => 'validOn',
                        'required'    => false,
                        'description' => 'Returns object as valid on a given date time',
                        'schema'      => ['type'=>'string', 'format' => 'date-time'],
                        'in'          => 'query',
                    ];
                    $call['parameters'][] = [
                        'name'        => 'validFrom',
                        'required'    => false,
                        'description' => 'Returns objects valid from a given date time',
                        'schema'      => ['type'=>'string', 'format' => 'date-time'],
                        'in'          => 'query',
                    ];
                    $call['parameters'][] = [
                        'name'        => 'validUntil',
                        'required'    => false,
                        'description' => 'Returns objects valid until a given date time',
                        'schema'      => ['type'=>'string', 'format' => 'date-time'],
                        'in'          => 'query',
                    ];
                }
            }
        }

        /* @todo dit afbouwen */

        /*
        if(config heltchecks is true){
            $tag=[];
            $tag['name']='';
            $tag['description']='';
            array_unshift($fruits_list, $tag);

        }

        if(config audittrail is true){
            $tag=[];
            $tag['name']='';
            $tag['description']='';
            array_unshift($fruits_list, $tag);

        }

        if(config notifications is true){
            $tag=[];
            $tag['name']='';
            $tag['description']='';
            array_unshift($fruits_list, $tag);

        }

        if(config authorization is true){
            $tag=[];
            $tag['name']='';
            $tag['description']='';
            array_unshift($fruits_list, $tag);
        }
        */
        //var_dump($docs);

        // Aditional tags

        // Security tag
        if (getenv('HEALTH_ENABLED') == 'true') {
            $tag = [];
            $tag['name'] = 'Health Checks';
            $tag['description'] = 'Authorization';
            $tag['externalDocs'] = [];
            $tag['externalDocs'][] = ['url'=>'http://docs.my-api.com/pet-operations.htm'];
            array_unshift($docs['tags'], $tag);
        }

        // Security tag
        if (getenv('NOTIFICATION_ENABLED') == 'true') {
            $tag = [];
            $tag['name'] = 'Notifications';
            $tag['description'] = 'Authorization';
            $tag['externalDocs'] = [];
            $tag['externalDocs'][] = ['url'=>'http://docs.my-api.com/pet-operations.htm'];
            array_unshift($docs['tags'], $tag);
        }

        // Security tag
        if (getenv('AUDITTRAIL_ENABLED') == 'true') {
            $tag = [];
            $tag['name'] = 'Audit trail';
            $tag['description'] = 'Authorization';
            $tag['externalDocs'] = [];
            $tag['externalDocs'][] = ['url'=>'http://docs.my-api.com/pet-operations.htm'];
            array_unshift($docs['tags'], $tag);
        }

        // Security tag
        if (getenv('AUTH_ENABLED') == 'true') {
            $tag = [];
            $tag['name'] = 'Authorization';
            $tag['description'] = 'Authorization';
            $tag['externalDocs'] = [];
            $tag['externalDocs'][] = ['url'=>'http://docs.my-api.com/pet-operations.htm'];
            array_unshift($docs['tags'], $tag);
        }

        //var_dump($docs);
        return $docs;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    private function getAdditionalEntityDocs($entity)
    {
        $metadata = $this->em->getClassMetadata($entity);
        $reflector = $metadata->getReflectionClass();
        $properties = $metadata->getReflectionProperties();
        $annotations = $this->annotationReader->getClassAnnotations($reflector);
        $additionalDocs = ['properties', 'security'=>[]];
        $required = [];

        // Add audittrail
        // Add healthcheck

        $class = $reflector->getShortName();
        $path = '/'.$this->camelCaseToSnakeCaseNameConverter->normalize($class);

        // Lets take a look at the properties an annotions,
        foreach ($properties as $property) {

            // The dockBlocks for thie property
            $factory = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
            $docblock = $factory->create($property->getDocComment());
            $tags = $docblock->getTags();
            $atributes = [];
            $groups = [];

            foreach ($tags as $tag) {
                $name = $tag->getName();
                $description = $tag->getDescription();

                switch ($name) {

                    // Description
                    case 'var':
                        $atributes['description'] = (string) $description;
                        $atributes['type'] = (string) $tag->getType();
                        
                        // Lets check on objects                        
                        $chr = mb_substr ($atributes['type'], 0, 1, "UTF-8");
                        $skip = ['UuidInterface','Datetime'];
                        $strip = ['\\','[',']'];
                        $clean = str_replace($strip,'', $atributes['type']);
                        if("\\" == $chr && !in_array($clean,$skip)){      
                        	// We have an object
                        	$atributes['eaxample'] = '#/components/schemas/'.$clean.'-read'; 
                        	$atributes['type'] = 'object';
                        	$atributes['format'] = $clean;
                        }
                        
                        break;
                        
                    // Docblocks
                    case 'example':
                        $atributes['example'] = (string) $description;
                        break;

                    // Groups
                    case 'Groups':
                        $propertyAnnotation = $this->annotationReader->getPropertyAnnotation($property, "Symfony\Component\Serializer\Annotation\Groups");
                        $groups = $propertyAnnotation->getGroups();
                        break;

                        // Constrainds (Validation)
                    case "Assert\Date":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'date';
                        $atributes['example'] = \date('Y-m-d');
                        break;
                    case "Assert\DateTime":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'date-time';
                        $atributes['example'] = \date('Y-m-d').'T'.\date('H:i:s').'+00:00';
                        break;
                    case "Assert\Time":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'time';
                        $atributes['example'] = \date('H:i:s');
                        break;
                    case "Assert\Timezone":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'timezone';
                        $atributes['example'] = 'America/New_York';
                        break;
                    case "Assert\Uuid":
                    	$atributes['type'] = 'string'; 
                    	$atributes['format'] = 'uuid';
                    	$atributes['example'] = '9b9eea1a-ef04-427d-b8bd-7f5c24801874';
                        break;
                    case "Assert\Email":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'email';
                        break;
                    case "Assert\Url":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'url';
                        break;
                    case "Assert\Regex":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'regex';
                        break;
                    case "Assert\Ip":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'ip';
                        break;
                    case "Assert\Json":
                        $atributes['type'] = 'string';
                        $atributes['format'] = 'json';
                        break;
                    case "Assert\Choice":
                        //@todo
                        //$atributes['format'] = 'json';
                        break;                      

                    case "Assert\NotNull":
                        $required[] = $property->name;
                        break;
                    case "Assert\Length":
                        $propertyAnnotation = $this->annotationReader->getPropertyAnnotation($property, "Symfony\Component\Validator\Constraints\Length");
                        if ($propertyAnnotation->max) {
                            $atributes['maxLength'] = $propertyAnnotation->max;
                        }
                        if ($propertyAnnotation->min) {
                            $atributes['minLength'] = $propertyAnnotation->min;
                        }
                        break;
                    case "Assert\Valid":
                    	//@todo
                    	// this means tha we haven an object on our hands;
                    	break;
                }
            }
            // Lets write everything to the docs
            foreach ($groups as $group) {
                //$additionalDocs["components"]['schemas'][$class."-".$group] = $atributes;
                $additionalDocs['properties'][$class.'-'.$group]['properties'][$property->name] = $atributes;
                $additionalDocs['properties'][$class.'-'.$group]['required'] = $required;

                if (!array_key_exists($group, $additionalDocs['security'])) {
                    $additionalDocs['security'][$group] = $group.' right to the '.$class.' resource';
                }
            }
        }

        return $additionalDocs;
    }
}
