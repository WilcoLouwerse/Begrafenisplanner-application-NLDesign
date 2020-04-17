# Design Considerations as Proposal

Welcome, you are currently viewing the design decisions for the proto component. The proto component aims to provide a framework for the quick development of production apis for the commonground project.

*Index*
- [The European factor](#the-european-factor)
- [On standards and standardisation](#on-standards-and-standardization)
- [NL API Strategie](#nl-api-strategie)

*Design Choices*
- [NLX](#nlx)
- [English](#english)
- [Fields](#fields)
- [Search](#search)
- [Queries](#queries)
- [Extending](#extending)
- [Time travel](#timetravel)
- [Archivation](#archivation)
- [Audit trail](#audittrail)
- [Health checks](#healthchecks)
- [Notifications](#notifications)
- [Authentication](#authentication)
- [Authorization](#authorization)
- [Ordering](#ordering)
- [Translations](#translations)
- [Errors](#errors)
- [Arrays](#arrays)
- [Filtering](#filtering)

*Implementation choices*
- [Api Versioning](#api-versioning)
- [Environments and name spacing](#environments-and-namespacing)
- [Domain Build-up and routing](#domain-build-up-and-routing)
- [Container Setup](#container-setup)


The European factor
-------
The proto-component isn't just a Dutch Component, it is in essence a Dutch translation of European components, nowhere is this more obvious than in the core code. Our component is based on [API Platform](https://api-platform.com/) an API specific version of the symfony framework. This framework is build by the lovely people of [Les Tilleuls](https://les-tilleuls.coop/en) and is build with support of the European Commission trough the [EU-FOSSA Hackathon](https://ec.europa.eu/info/news/first-eu-fossa-hackathon-it-happened-2019-may-03_en) and Digital Ocean trough [Hacktoberfest](https://hacktoberfest.digitalocean.com/).

But it doesn't just end there. The [varnish container](https://hub.docker.com/r/eeacms/varnish/) that we use to speed up the API response is build and maintained by [EEA]() (The European Environment Agency) and the development team at conduction itself is attached to the [Odyssey program](https://www.odyssey.org/) and originated from the [startupinresidence](https://startupinresidence.com/) program. 

So you could say that both change and a European perspective is in our blood.

On standards and standardization
-------
The specific goal of the proto component (which this current code base is a version of) is to provide a common architecture for common ground components. As such the common ground principles are leading in design choices, and within those principles international compliancy and technological invocation is deemed most important. **We do not want to make concessions to the current infrastructure.** As such the component might differ on [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie), [NORA](https://www.noraonline.nl/wiki/Standaarden), [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/index) and or other standards if they are deemed incompatible or out of line with (inter)national standards and or good practices. 

Unfortunately (inter)national standards can be conflicting. We therefore prioritize standards on several grounds

- International is put before local
- Standards carried by a standard organization (like ISO, W3C etc) at put before floating standards (like RFC's) wichs are put before industry standards, good practices and so on. 

So if for instance a **local** standard is out of line with an **international** good practice we follow the international good practice.

### Commonground specific standards

This component was designed in line with the [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie), [NORA](https://www.noraonline.nl/wiki/Standaarden), [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/index), [commonground principles](https://vng.nl/onderwerpenindex/bestuur/samen-organiseren-2019/common-ground). 

## NL API Strategie

The [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie) takes a special place in this component, it is designed as a set of guidelines for API's for the Dutch landscape. As such we follow it as close as possible. It dos however contains inconsistencies with both international standards and good practices. On those items we do not follow the norm but consider it our duty to try to change the norm. 

** We implement **

api-01, api-02, api-03, api-05, api-06, api-10, api-11, api-12, api-13,api-14, api-16, api-18, api-19, api-20, api-21, api-22, api-23, api-24, api-25, api-26, api-27, api-28, api-29, api-30, api-33, api-34, api-35, api-42

** We want to implement **
- [api-14](https://docs.geostandaarden.nl/api/API-Strategie/#api-14) Use OAuth 2.0 for authorization

** We do not implement **

- [api-04](https://docs.geostandaarden.nl/api/API-Strategie/#api-04) Define interfaces in Dutch unless there is an official English glossary (see [english](#english))
- [api-09](https://docs.geostandaarden.nl/api/API-Strategie/#api-09) Implement custom representation if supported (see [fields](#fields))
- [api-17](https://docs.geostandaarden.nl/api/API-Strategie/#api-17) Publish documentation in Dutch unless there is existing documentation in English or there is an official English glossary (see [english](#english))
- [api-31](https://docs.geostandaarden.nl/api/API-Strategie/#api-31) Use the query parameter sorteer to sort (see [ordering](#ordering))
- [api-32](https://docs.geostandaarden.nl/api/API-Strategie/#api-32) Use the query parameter zoek for full-text search (see [search](#search))
- [api-36](https://docs.geostandaarden.nl/api/API-Strategie/#api-36) Provide a POST endpoint for GEO queries (see [queries](#queries))
- [api-37](https://docs.geostandaarden.nl/api/API-Strategie/#api-37) Support mixed queries at POST endpoints available (see [queries](#queries))
- [api-38](https://docs.geostandaarden.nl/api/API-Strategie/#api-38) Put results of a global spatial query in the relevant geometric context (see [queries](#queries))


** We doubt or haven�t made a choice yet about**

- [api-15](https://docs.geostandaarden.nl/api/API-Strategie/#api-15) Use PKI overheid certificates for access-restricted or purpose-limited API authentication
- [api-39](https://docs.geostandaarden.nl/api/API-Strategie/#api-39) Use ETRS89 as the preferred coordinate reference system (CRS)
- [api-40](https://docs.geostandaarden.nl/api/API-Strategie/#api-40) Pass the coordinate reference system (CRS) of the request and the response in the headers
- [api-41](https://docs.geostandaarden.nl/api/API-Strategie/#api-41) Use content negotiation to serve different CRS

NLX 
-------
We implement the [NLX system](https://docs.nlx.io/understanding-the-basics/introduction/) as part of the basic commonground infrastructure, as such nlx headers are used in the internal logging.
The following X-NLX headers have been implemented for that reason `X-NLX-Logrecord-ID`,`X-NLX-Request-Process-Id`,`X-NLX-Request-Data-Elements` and `X-NLX-Request-Data-Subject`, these are tied to the internal audit trail (see audit trail for more information), and `X-Audit-Toelichting` (from the ZGW APIs) is implemented as `X-Audit-Clarification`. We do not use other NLX headers since they (conform to the [NLX schema](https://docs.nlx.io/reference-information/transaction-log-headers/)) wil not reach the provider. 

We strongly discourage the use of the `X-NLX-Request-Data-Subject` header as it might allow private data (such as BSNs) to show up in logging.

Please note that the use of nlx is optional. The component can be used without NLX. In that case set `X-NLX-Logrecord-ID` to false and provide (the normaly ignored)  fields `X-NLX-Requester-User-Id`, `X-NLX-Request-Application-Id`, `X-NLX-Request-Subject-Identifier`, `X-NLX-Requester-Claims` and `X-NLX-Request-User` as if you are making an NLX call. This provides the API with enough credentials to make an complete audit trail. It also provides an easy implementation route to NLX since the only thing that would need to be changed at a later time is making you call to an nlx outway instead of the API directly. 

English
-------
The [NL API Standard](https://geonovum.github.io/KP-APIs/#api-04-define-interfaces-in-dutch-unless-there-is-an-official-english-glossary) describes that there is a preference for Dutch in API documentation.

> Define resources and the underlying entities, fields and so on (the information model ad the external interface) in Dutch. English is allowed in case there is an official English glossary.

We view this as a breach with good coding practice and international coding standards, all documentation and code is therefore supplied in English. We do however provide translation (or i18n) support. 

Fields
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Persoon/openapi.yaml) the concept of field limitations has been introduced its general purpose being to allow an application to limit the returned fields to prevent the unnecessary transportation of (private) data. In the [NL API Strategie](https://github.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/blob/master/features/fields.feature) this has been implemented as a parameter consisting of comma separated values. However the normal web standard for optional lists (conform w3c form standards) is an array.

Search
-------
As part of [api-32](https://docs.geostandaarden.nl/api/API-Strategie/#api-32) a `zoeken` query has been introduced that can handle wildcards. This breaks best practice, first of allest practice is a `search` query parameter (see also the nodes on [English](#english)). Secondly wildcards are a sql concept, not a webconcept, they are also a rather old concept severely limiting the search options provided. Instead the [regular expression standard](https://en.wikipedia.org/wiki/Regular_expression) should be used. 

__solution__
We implement a `search` query parameter on resource collections, that filters with regex. 

Queries
-------
In several examples of the nl api strategie we see query parameters being attached to post requests. This is unusual in the sence that sending query strings along with a post is considered bad practice (because query parameters end up as part of an url and are therefore logged by servers). But it is technically possible folowing RFC 3986. The real pain is that in the NL api-stratgie the POST requests seems to be used to search, ot in other words GET data. This is where compliance with HTTP (1.1) breaks.  
   
__solution__
We do not implement a query endpoint on post requests.

Api Versioning
-------
As per [landelijke API-strategie.](https://geonovum.github.io/KP-APIs/#versioning) we provide/ask major versions in the endpoint and minor versions in header, for this the `API-Version` is used (instead of the `api-version` header used in haal centraal)

__solution__
We implement both endpoint and header versioning

Extending
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Perso on/openapi.yaml) the concept of extending has been introduced, its general purpose being to allow the inclusion of sub resources at an api level thereby preventing unnecessary API calls. In the [NL API Strategie](https://github.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/blob/master/features/expand.feature) this has been implemented as a parameter consisting of comma separated values. However the normal web standard for optional lists (conform w3c form standards) is an array.

__solution__
The extend parameter has been implemented as an array

Archivation
-------
There is a need (by law) for archivation, meaning that we should only keep resources for a fixed amount of time and delete them thereafter. In line with the extending and fields principle whereby we only want resource properties that we need when we needed, it is deemed good practice make a sub resource of the archivation properties. For the archivation properties the [zgw](https://zaken-api.vng.cloud/api/v1/schema/#operation/zaak_list) is followed and translated to englisch. 


```json
{
	"id": "e2984465-190a-4562-829e-a8cca81aa35d",
	"nomination": "destroy",
	"action_date": "2019-11-25T07:26:54Z",
	"status": "to_be_archived",
}
```

This gives us an interesting thought, according to [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie/#api-10-implement-operations-that-do-not-fit-the-crud-model-as-sub-resources) sub resources should have their own endpoint. Therefore we could use a archive sub of a different resource for archivation rules e.g. /zaken/{uuid}/archivation for a verzoek. This in itself leads credence to the thought that archivation should have its own central crud api.


Audittrail
-------
For audittrail we use the base mechanism as provided by [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/audit-trail), we do however diver on some key point,
- Personal data should never be part of a log, therefore only the user id with the client should be logged (instead of the name)
- Besides an endpoint per resource there should be a general enpoint to search all audit trials of a component
- [Time travel](#timetravel) in combination with objects versioning makes the return of complete objects unnecessary. But an audit rail endpoint should support the [extend](#extending) functionality to provide the option of obtaining complete objects.  


__solution__
In compliance with [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/audit-trail) each individual object should support an /audittrail endpoint. You can look into the [tutorial](TUTORIAL.md) for specifications on how to activate an audit trail for a given object. 

Healthchecks
-------
From [issue 154](https://github.com/VNG-Realisatie/huwelijksplanner/issues/154)

For healthchecks we use the health-json principle (or json-health to stay in line with json-ld and json-hal). This means the any endpoint `should` be capable of providing health information concerning that endpoint and services behind it.

__solution__
The use of a `Content-Type: application/health+json` header returns an health json schema.


Notifications
-------
For notifications we do not YET use the current [ZGW standard](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/notificaties) since there is an [dicusion](https://github.com/VNG-Realisatie/gemma-zaken/issues/1427#issuecomment-549272696) about the possible insecurity of sending properties or data objects along with a notification. It also doesn�t follow the [web standard](https://www.w3.org/TR/websub/). We wait for the conclusion of that discussion before making an implementation. 

__solution__
In compliance with [w3.org](https://www.w3.org/TR/websub/) each endpoint `should` returns an header containing an subscription url. That can be used in accordance with the application to subscribe to both individual objects as collections whereby collections serve as 'kanalen'. We aim to implement the ZGW notificatie component, but feel that further features on that component would be required to make to be fully supported. We will supply feature requests per issue to support this effort.

Authentication 
-------

__solution__

Authorization
-------
We implement user scopes as per [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/autorisatie-scopes) standard. But see problems with how the scopes are defined and named, and consider the general setup to be to focused on ZGW (including Dutch naming, zgw specific fields like maxVertrouwlijkheid and a lack of CRUD thinking). There is a further document concerning [Authentication and Authorization](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/authenticatie-autorisatie) that details how we should authenticate users and give them scopes. We agree with the principles of the document on application based authorization and the use of JWT tokens. But disagree on some key technical aspect. Most important being that the architecture doesn't take into consideration the use of one component by several organizations at once. Or scopes per property.

__solution__
No solution as of yet, so there is no implementation of Authorization or Scopes. We aim to implement the ZGW authorisatie component, but feel that further features on that component would be required to make to be fully supported. We will supply feature requests per issue to support this effort.


Timetravel
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Persoon/openapi.yaml) the concept of timetravel has been introduced, as in getting the version of an object as it was on a given date. For this the `geldigop` [see the docs](file:///C:/Users/ruben/Desktop/doc_gba_historie.html#operation/getBewoningen) header is used. In addition the `geldigvan` and `geldigtot` are introduced as collection filters. 

The commonground proto componant natively supports time traveling on all resources that are annotaded with the @Gedmo\Loggable, this is done by adding the ?validOn=[date] query to a request, date can either be a datetime or datedatime string. Any value supported by php's [strtotime()](https://www.php.net/manual/en/function.strtotime.php) is supported. Keep in mind that this returns the entity a as it was valid on that time or better put, the last changed version BEFORE that moment. To get a complete list of all changes on an item the [/audittrail](#Audittrail
) endpoint can be used.

__solution__
In compliance with [schema.org](https://schema.org/validFrom) `geldigop`,`geldigvan` and `geldigtot` are implemented as `validOn`,`validFrom` and `validUntil`. And can be used a query parameters on collection operations/

Additionally `validOn` can be used on a single object get request to get the version of that object on a given date, a 404 is returned if no version of that object can be given for the given date 

Ordering
-------
In the [zaak-api](https://zaken-api.vng.cloud/api/v1/schema/#operation/zaak_list) ordering is done in a single field parameter, we however prefer to be able to order on multiple fields in combination of ascending and descending orders. We therefore implement an order parameter as array where they key is the property on wish should be ordered and the value the type of ordering e.g. `?order[name]=desc&order[status]=asc`. The order in which the keys are added to the order array determines the order in which they are applied.    


Translations
-------
We support translations trough the `Accept-Language` header (read the [docs](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language)), the fallback language for all messages is English

Errors
-------
See [jsonapi](https://jsonapi.org/examples/#error-objects) and the [rfc](https://tools.ietf.org/html/rfc7807). 

Arrays
-------
The NL API standard uses comma notation on array's in http requests. E.g. fields=id,name,description however common browsers(based on chromium e.g. chrome and edge) use bracket notation for query style array's e.g. fields[]=id&fields[]=name,&fields[]=description. The difference of course is obvious since comma notation doesn't allow you to index arrays. [Interestingly enough there isn't actually a rfc spec for this](https://stackoverflow.com/questions/15854017/what-rfc-defines-arrays-transmitted-over-http). 

It is perceivable that in future iterations we would like to use indexed array in situations where the index of the array can't be assumed on basis of url notation, when indexes aren�t numerical, when we don�t want an index to start at 0 or when indexes are purpusly missing (comma notation of id,name,description would always refert to the equivalent of fields: [
  0 => id,
  1 => name,
  2 => description
]

__solution__
We support both comma and bracket notation on array's, but only document bracket notation since it is preferred.


Container Setup
-------
https://medium.com/shiphp/building-a-custom-nginx-docker-image-with-environment-variables-in-the-config-4a0c36c4a617
 

Filtering
-------
Because it is based on api-platform de proto component supports filter functions as in [api platform](https://api-platform.com/docs/core/filters/) additionally there are custom filter types available for common ground. 

__Regex Exact__

__Regex Contains__

__Like___
The like filters is used to search for resources with the traditional sql LIKE operator. If pattern does not contain percent signs or underscores, then the pattern only represents the string itself; in that case LIKE acts like the equals operator. An underscore (_) in pattern stands for (matches) any single character; a percent sign (%) matches any sequence of zero or more characters.

Some examples:

'abc' LIKE 'abc'    true
'abc' LIKE 'a%'     true
'abc' LIKE '_b_'    true
'abc' LIKE 'c'      false

LIKE pattern matching always covers the entire string. Therefore, if it's desired to match a sequence anywhere within a string, the pattern must start and end with a percent sign.

To match a literal underscore or percent sign without matching other characters, the respective character in pattern must be preceded by a backlash. 

## Kubernetes

### Loadbalancers
We no longer provide a load balancer per component, since this would require a IP address per component (and ipv 4 addresses are in short supply). Instead we make components available as an internal service. A central load balancer could then be used to provide several api�s in one 

### server naming
A component is (speaking in kubernetes terms) a service that is available at a name corresponding to its designation 

### Domain Build-up and routing
By convention the component assumes that you follow the common ground domain name build up, meaning {environment}.{component}.{rest of domain}. That means that only the first two url parts are used for routing. It is also assumed that when no environment is supplied the production environment should be offered E.g. a proper domain for the production API of the verzoeken registratie component would be prod.vrc.zaakonline.nl but it should also be reachable under vrc.zaakonline.nl. The proper location for the development environment should always be dev.vrc.zaakonlin.nl

### Environments and namespacing
We assume that for that you want to run several environments for development purposes. We identify the following namespaces for support.
- prod (Production)
- acce (Acceptation)
- stag (Staging)
- test (Testing)
- dev (Development)

Because we base the common ground infrastructure on kubernetes, and we want to keep a hard separation between environment we also assume that you are using your environment as a namespace

Symfony library management gives us the option to define the libraries on a per environment base, you can find that definition in the [bundle config](api/config/bundles.php)

Besides the API environments the component also ships with additional tools/environments but those are not meant to be deployed
- client (An react client frontend)
- admin (An read admin interface)

On the local development docker deploy the client environment is used as default instead of the production version of the api.


## Data types

| Period Designator | Description                                                          |
|-------------------|----------------------------------------------------------------------|
| Y                 | years                                                                |
| M                 | months                                                               |
| D                 | days                                                                 |
| W                 | weeks. These get converted into days, so cannot be combined with D. |
| H                 | hours                                                                |
| M                 | minutes                                                              |
| S                 | seconds                                                              |

### Types versus formats

| Type    | Format    | Example  | Source | Description | Documentation                                                        |
|---------|-----------|----------|--------|-------------|----------------------------------------------------------------------|
| integer | int32     |          |        |             |                                                                      |
| integer | int64     |          |        |             |                                                                      |
| string  | float     | 0.15625  |        |             | [wikipedia](https://en.wikipedia.org/wiki/Single-precision_floating-point_format) |
| string  | double    | 0.15625  |        |             | [wikipedia](https://en.wikipedia.org/wiki/Double-precision_floating-point_format) |
| integer | byte      |          |        |             |                                                                      |
| integer | binary    |          |        |             |                                                                      |
| string  | date      |          |        |             |                                                                      |
| string  | date-time |          |        |             |                                                                      |
| string  | duration  | P23DT23H |        |             | [wikipedia](https://en.wikipedia.org/wiki/ISO_8601#Durations)                     |
| string  | password  |          |        |             |                                                                      |
| string  | boolean   |          |        |             |                                                                      |
| string  | string    |          |        |             |                                                                      |
| string  | uuid      |          |        |             |                                                                      |
| string  | uri       |          |        |             |                                                                      |
| string  | email     |          |        |             |                                                                      |
| string  | rsin      |          |        |             |                                                                      |
| string  | bag       |          |        | A BAG uuid  |                                                                      |
| string  | bsn       |          |        |             |                                                                      |
| string  | iban      |          |        |             |                                                                      |
|         |           |          |        |             |                                                                      |


