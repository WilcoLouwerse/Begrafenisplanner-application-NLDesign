# Design Considerations

This component was designed in line with the [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie), [NORA](https://www.noraonline.nl/wiki/Standaarden), [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/index), [commonground principles](https://vng.nl/onderwerpenindex/bestuur/samen-organiseren-2019/common-ground) and international standards. 

The specific goal of this component is to provide a common architecture for common ground components as such the common ground principles are leading in design choices, and within those principles technological invocation and international compliancy is deemed most important. **We do not want to mace consessions to the current infrastructure.** As such the component might differ on  [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie), [NORA](https://www.noraonline.nl/wiki/Standaarden), [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/index) and other standards if they are deemed incompatible or out of line with international standards. 

The European factor
-------
The proto-component isn't just a Dutch Component, it is in essence a dutch translation of european components, nowhere is this more obvious than in the core code. Our component is based on [API Platform](https://api-platform.com/) an API specific version of the symfony framework. This framework is build by the lovely people of []() and is build with support of the European Commission trough the [EU-FOSSA Hackathon](https://ec.europa.eu/info/news/first-eu-fossa-hackathon-it-happened-2019-may-03_en) and Digital Ocean trough [Hacktoberfest](https://hacktoberfest.digitalocean.com/).

But it doesn't just end there. The [varnish container](https://hub.docker.com/r/eeacms/varnish/) that we use to speed up the API responce it build and maintained by [EEA]() (The European Environment Agency) and the development team at conduction itself is attached to the [Odyssey program](https://www.odyssey.org/) and originated from the [startupinresidence](https://startupinresidence.com/) program. 

So you could say that both change and a european perspective is in our blood.


Domain Build-up and routing
-------
By convention the component assumes that you follow the common ground domain name build up, meaning {environment}.{component}.{rest of domain}. That means that only the first two url parts are used for routing. It is also assumed that when no environment is supplied the production environment should be offered E.g. a proper domain for the production API of the verzoeken registratie component would be prod.vrc.zaakonline.nl but it should also be reachable under vrc.zaakonline.nl. The proper location for the development environment should always be dev.vrc.zaakonlin.nl

Environments and namespacing
-------
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

Logging Headers (NLX Audit trail)
-------
@todo update, a reaction about this has been given by the NLX team.

We inherit a couple of headers from the transaction logging within the [NLX schema](https://docs.nlx.io/further-reading/transaction-logs/), we strongly discourage the use of the `X-NLX-Request-Data-Subject` header as it might allow private data (such as BSNs) to show up in logging.

__solution__
The following X-NLX headers have been implemented `X-NLX-Logrecord-ID`,`X-NLX-Request-Process-Id`,`X-NLX-Request-Data-Elements` and `X-NLX-Request-Data-Subject`, these are tied to the internal audit trail (see audit trail for more information), and `X-Audit-Toelichting` (from the ZGW APIs) is implemented as `X-Audit-Clarification`

Api versioning
-------
As per [landelijke API-strategie.](https://geonovum.github.io/KP-APIs/#versioning) major versions in endpoint minor versions in header, for this the `API-Version` is used (instead of the `api-version` header used in haal centraal)

Fields
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Persoon/openapi.yaml) the concept of field limitations has been introduced its general purpose being to allow an application to limit the returned fields to prevent the unnecessary transportation of (private) data. In the [NL API Strategie](https://github.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/blob/master/features/fields.feature) this has been implemented as a parameter consisting of comma separated values. However the normal web standard for optional lists (conform w3c form standards) is an array.

__solution__
The fields parameter and functionality has been implemented as an array

Extending
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Perso on/openapi.yaml) the concept of extending has been introduced, its general purpose being to allow the inclusion of sub resources at an api level thereby preventing unnecessary API calls. In the [NL API Strategie](https://github.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/blob/master/features/expand.feature) this has been implemented as a parameter consisting of comma separated values. However the normal web standard for optional lists (conform w3c form standards) is an array.

__solution__
The extend parameter has been implemented as an array

Archivation
-------
In line with the extending and fields principle whereby we only want resources that we need it was deemed, nice to make a sub resource of the archivation properties. This also results in a bid cleaner code.  


Audittrail
-------
@todo this needs to be implemented
For notifications we use the base mechanism as provided by [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/audit-trail) but we differ on insight into the data that should be returned and feel that the international standard [RFC 3881](https://tools.ietf.org/html/rfc3881) should have been followed here.

__solution__
In compliance with [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/audit-trail) each individual object should support an /audittrail endpoint. You can look into the [tutorial](TUTORIAL.md) for specifications on how to activate an audit trail for a given object. However, instead of the values mention in the vng.cloud design we follow [RFC 3881](https://tools.ietf.org/html/rfc3881) for the return values. And we give NLX values precedence if provided.

Notifications
-------
@todo this needs to be implemented
For notifications we do not use the current [ZGW standard](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/notificaties) since we deem it insecure to send properties or data objects along with a notification. This is a potential security breach explained [here](https://github.com/VNG-Realisatie/gemma-zaken/issues/1427#issuecomment-549272696).  It also doesn�t follow the [web standard](https://www.w3.org/TR/websub/). Instead we are developing our own subscriber service that is tailored for the NLX / VNG environment and based on current web standards [here]().

__solution__
In compliance with [w3.org](https://www.w3.org/TR/websub/) each endpoint returns an header containing an subscribtion url. That can be used in acordanse with the application to subscribe to both individual objects as collections. whereby collections serve as 'kanalen'.

Scopes, Authentication and Authorization
-------
@todo this needs to be implemented
We implement user scopes as per [vng.cloud](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/autorisatie-scopes) standard. But see problems with how the scopes are defined and named, and consider the general setup to be to focused on ZGW (including Dutch naming, zgw specific fields like maxVertrouwlijkheid and a lack of CRUD thinking). There is a further document concerning [Authentication and Authorization](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/authenticatie-autorisatie) that details how we should authenticate users and give them scopes. We agree with the principles of the document on application based authorization and the use of JWT tokens. But disagree on some key technical aspect. Most important being that the architecture doesn't take into consideration the use of one component by several organizations

__solution__
No solution as of yet, so there is no implementation of Authorization or Scopes. We might build a new Authorization Component in the long run.


Timetravel
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Persoon/openapi.yaml) the concept of timetravel has been introduced, as in getting the version of an object as it was on a given date. For this the `geldigop` [see the docs](file:///C:/Users/ruben/Desktop/doc_gba_historie.html#operation/getBewoningen) header is used. In addition the `geldigvan` and `geldigtot` are introduced as collection filters. 

The commonground proto componant natively supports time traveling on all entities that are annotaded with the @Gedmo\Loggable, this is done by adding the ?validOn=[date] query to a request, date can either be a datetime or datedatime string. Any value supported by php's [strtotime()](https://www.php.net/manual/en/function.strtotime.php) is supported. Keep in mind that this returns the entity a as it was valid on that time or better put, the last changed version BEFORE that moment. To get a complete list of all changes on a item the ?showLogs=true quarry can be used.

__solution__
In compliance with [schema.org](https://schema.org/validFrom) `geldigop`,`geldigvan` and `geldigtot` are implemented as `validOn`,`validFrom` and `validUntil`. And can be used a query parameters on colelction operations.

Additionally `validOn` can be used on a single object get request to get the version of that object on a given date, a 404 is returned if no version of that object can be given for that date 

Ordering results
-------
In the [zaak-api](https://zaken-api.vng.cloud/api/v1/schema/#operation/zaak_list) ordering is done in a single field parameter, we however prefer to be able to order on multiple fields in combination of ascending and descending orders. We therefore implement an order parameter as array where they key is the property on wish should be ordered and the value the type of ordering e.g. `?order[name]=desc&order[status]=asc`. The order in which the keys are added to the order array determines the order in which they are applied.    

Dutch versus English
-------
The [NL API Standard](https://geonovum.github.io/KP-APIs/#api-04-define-interfaces-in-dutch-unless-there-is-an-official-english-glossary) describes that there is a preference for Dutch in API documentation.

> Define resources and the underlying entities, fields and so on (the information model ad the external interface) in Dutch. English is allowed in case there is an official English glossary.

We view this as a breach with good coding practice and international coding standards, all documentation is therefore supplied in English



Comma Notation versus Bracket Notation on arrays's
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
The like filters is used to search for enities with the traditional sql LIKE operator. If pattern does not contain percent signs or underscores, then the pattern only represents the string itself; in that case LIKE acts like the equals operator. An underscore (_) in pattern stands for (matches) any single character; a percent sign (%) matches any sequence of zero or more characters.

Some examples:

'abc' LIKE 'abc'    true
'abc' LIKE 'a%'     true
'abc' LIKE '_b_'    true
'abc' LIKE 'c'      false
LIKE pattern matching always covers the entire string. Therefore, if it's desired to match a sequence anywhere within a string, the pattern must start and end with a percent sign.

To match a literal underscore or percent sign without matching other characters, the respective character in pattern must be preceded by a backlash. 

## Kubernetes

### Loadbalancers
We no longer provide a load balancer per component, since this would require a ip per component. Draining ip's on mult component kubernetes clusters. In stead we make componentes available as an interner service

### server naming
A component is (speaking in kubernetes terms) a service that is available at 

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
