#Design Considerations

This component was designed inline with the [NL API Strategie](https://docs.geostandaarden.nl/api/API-Strategie) and [https://www.noraonline.nl/wiki/Standaarden](NORA).

Loging Headers (NLX Audit trail)
-------
We inherit a couple of headers from the transaction logging within the [NLX schema](https://docs.nlx.io/further-reading/transaction-logs/), there does however see to be on ongoing discussion on how headers are supposed to be interpreted. The NLX schema states 
> The outway appends a globally unique `X-NLX-Request-Id` to make a request traceable through the network. All the headers are logged before the request leaves the outway. Then the fields `X-NLX-Request-User-Id`, `X-NLX-Request-Application-Id`, and `X-NLX-Request-Subject-Identifier` are stripped of and the request is forwarded to the inway*

This would sugjest that no `X-NLX-Request-User-Id` should be present on an endpoint (since it would have been stripped before getting there) but a `X-NLX-Request-Id` should be present. If we look at the open case API however exactly the opposite has been implemented. Also a new header `X-Audit-Toelichting` has been implemented that seems to be doing what `X-NLX-Request-Process-Id` is doing in the case of a known process

__solution__
All X-NLX headers are implemented for logging right now, and `X-Audit-Toelichting` is implemented as `X-Audit-Clarification`

Api versioning
-------
As per [landelijke API-strategie.](https://geonovum.github.io/KP-APIs/#versioning) major versions in endpoint minor versions in header, for this the `API-Version` is used (in stead of the api-version header used in haal centraal)

Fields
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Persoon/openapi.yaml) the concept of field limitations has been introduced its general purpose being to allow an application to limit the returned fields to prevent the unnecessary transportation of (private) data. In the [NL API Strategie](https://github.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/blob/master/features/fields.feature) this has been implemented as a parameter consisting of comma separated values. However the normal web standard for optional lists (conform w3c form standards) is an array.

__solution__
The fields parameter has been implemented as an array

Extending
-------
A part of the [haal centraal](https://raw.githubusercontent.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/master/api-specificatie/Bevraging-Ingeschreven-Perso on/openapi.yaml) the concept of extending has been introduced, its general purpose being to allow the inclusion of sub resources at an api level thereby preventing unnecessary API calls. In the [NL API Strategie](https://github.com/VNG-Realisatie/Haal-Centraal-BRP-bevragen/blob/master/features/expand.feature) this has been implemented as a parameter consisting of comma separated values. However the normal web standard for optional lists (conform w3c form standards) is an array.

__solution__
The extend parameter has been implemented as an array

Archivation
-------
In line with the extending and fields principle whereby we only want resources that we need it was deemed, nice to make a sub resource of the archivation properties. This also results in a bid cleaner code.  

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

It is perceivable that in future iterations we would like to use indexed array in situations where the index of the array can't be assumed on basis of url notation, when indexes aren’t numerical, when we don’t want an index to start at 0 or when indexes are purpusly missing (comma notation of id,name,description would always refert to the equivalent of fields: [
  0 => id,
  1 => name,
  2 => description
]

__solution__
We support both comma and bracket notation on array's, but only document bracket notation since it is preferred.

 

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
