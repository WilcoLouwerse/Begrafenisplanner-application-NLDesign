# About this component

Het "betalen" component is ontwikkeld voor de gemeente Utrecht en heeft als doel betalingen te registreren en af te handelen voor objecten uit andere componenten. Dit stelt je in staat om voor deze objecten facturen aan te maken en te beheren.

## Documentation

- [Installation manual](https://github.com/ConductionNL/betalencomponent/blob/master/INSTALLATION.md).
- [contributing](https://github.com/ConductionNL/betalencomponent/blob/master/CONTRIBUTING.md) for tips tricks and general rules concerning contributing to this component.
- [codebase](https://github.com/ConductionNL/betalencomponent) on github.
- [codebase](https://github.com/ConductionNL/betalencomponent/archive/master.zip) as a download.

Getting started
-------
Do you want to create your own Commonground component? Take a look at our in depht [tutorial](TUTORIAL.md) on spinning up your own component!

The commonground bundle
-------
This repository uses the power of conductions [commonground bundle](https://packagist.org/packages/conduction/commongroundbundle) for symfony to provide common ground specific functionality based on the [VNG Api Strategie](https://docs.geostandaarden.nl/api/API-Strategie/). Including  

* Build in support for public API's like BAG (Kadaster), KVK (Kamer van Koophandel)
* Build in validators for common dutch variables like BSN (Burger service nummer), RSIN(), KVK(), BTW()
* AVG and VNG proof audit trails
* And [muchs more](https://packagist.org/packages/conduction/commongroundbundle) .... 

Be sure to read our [design considerations](/design.md) concerning the [VNG Api Strategie](https://docs.geostandaarden.nl/api/API-Strategie/). 


Requesting features
-------
Do you need a feature that is not on this list? don't hesitate to send us a [feature request](https://github.com/ConductionNL/commonground-component/issues/new?assignees=&labels=&template=feature_request.md&title=).  

Staying up to date
-------

## Features
This repository uses the power of the [commonground proto component](https://github.com/ConductionNL/commonground-component) provide common ground specific functionality based on the [VNG Api Strategie](https://docs.geostandaarden.nl/api/API-Strategie/). Including  

* Build in support for public API's like BAG (Kadaster), KVK (Kamer van Koophandel)
* Build in validators for common dutch variables like BSN (Burger service nummer), RSIN(), KVK(), BTW()
* AVG and VNG proof audit trails, Wildcard searches, handling of incomplete date's and underInvestigation objects
* Support for NLX headers
* And [much more](https://github.com/ConductionNL/commonground-component) .... 

## License

Copyright &copy; [Gemeente Utrecht](https://www.utrecht.nl/)  2019 

Licensed under [EUPL](https://github.com/ConductionNL/betalencomponent/blob/master/LICENSE.md)

## Credits

[![Utrecht](https://raw.githubusercontent.com/ConductionNL/betalencomponent/master/resources/logo-utrecht.svg?sanitize=true "Utrecht")](https://www.utrecht.nl/)
[![Conduction](https://raw.githubusercontent.com/ConductionNL/betalencomponent/master/resources/logo-conduction.svg?sanitize=true "Conduction")](https://www.conduction.nl/)



