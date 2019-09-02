Design Considerations


Dutch versus English
-------
NL API Standard

__solution__
geldigOp is suported as a backup for validOn, but only validOn us documented.

Comma Notation versus Bracket Notation on arrays's
-------
The NL API standaard uses comma notation on array's in http requests. E.g. fields=id,name,description however common browsers(based on chromium e.g. chrome and edge) use bracker notation for query style array's e.g. fields[]=id&fields[]=name,&fields[]=description. The difrence ofcoure is obvius since comma notation doesn't allow you to index arrays. [Interestengly enough there isn't actually a rfc spec for this](https://stackoverflow.com/questions/15854017/what-rfc-defines-arrays-transmitted-over-http). 

It is precievable that in future iterations we would like to use indexd array in situations where the index of the array can't be assumed on basis of url notation, when indexes arn't numirical, when we dont want an index to start at 0 or when indexes are purpusly missing (comma notation of id,name,description would always refert to te equvalant of fields: [
  0 => id,
  1 => name,
  2 => description
]

__solution__
We support both comma and bracket notation on array's, but only document bracket notation since it is prevered.

Timetraveling
-------
The commonground proto componant natively supports time traveling on all entities that are annotaded with the @Gedmo\Loggable, this is done by adding the ?validOn=[date] query to a request, date can iether be a datetime or datedatime string. Ant value suported by php's [strtotime()](https://www.php.net/manual/en/function.strtotime.php) is suported. Keep in mind that this returns the entity a as it was valid on that time or better put, the last changed verion BEFORE that moment. To get a complete list of all changes on a item the ?showLogs=true quary can be used.
 
NLX Audit trail
-------
NLX uses [headers](https://docs.nlx.io/further-reading/transaction-logs/) for her audit trail, we curently log the following headers per request but dont validate on content thereof.
* X-NLX-Request-User-Id, the id of the user performing the request
* X-NLX-Request-Application-Id, the id of the application performing the request
* X-NLX-Request-Subject-Identifier, an subject identifier for purpose registration (doelbinding)
* X-NLX-Request-Process-Id, a process id for purpose registration (doelbinding)
* X-NLX-Request-Data-Elements, a list of requested data elements
* X-NLX-Request-Data-Subject 


JSON-HAL versus JSON-LD
-------