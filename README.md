
# Readme
-------
Welcome to the the VNG Common Ground proto component!

This "proto" component provides a plug and play solution for component generation on Common Ground. That means that it takes away all the hassle of setting op codebases, containers and following the VNG Api Standaard. It does all that for you! 

For that we use **[Api Platform](https://api-platform.com)**, a next-generation web framework designed to easily create API-first projects, without compromising extensibility and flexibility. 

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
-------
API Platform embraces open web standards (OpenAPI, JSON-LD, GraphQL, Hydra, HAL, JSONAPI, JWT, OAuth, HTTP...) and the [Linked Data](https://www.w3.org/standards/semanticweb/data) movement. Your API will automatically expose structured data in Schema.org/JSON-LD.
It means that your commonground application is usable **out of the box** with technologies of the semantic web.

* Comes with a paired [React](https://reactjs.org/) application, to provide face to your code
* And a fully functional (and automatically updated) [React Admin](https://marmelab.com/react-admin/) backend to easily test and proof your component
* Design your own data model as plain old PHP classes or [**import an existing one**](https://api-platform.com/docs/schema-generator)
  from the [Schema.org](https://schema.org/) vocabulary
* **Expose in minutes a hypermedia REST or a GraphQL API** with pagination, data validation, access control, relation embedding,
  filters and error handling...
* Benefit from Content Negotiation: [GraphQL](http://graphql.org), [JSON-LD](http://json-ld.org), [Hydra](http://hydra-cg.com),
  [HAL](http://stateless.co/hal_specification.html), [JSONAPI](https://jsonapi.org/), [YAML](http://yaml.org/), [JSON](http://www.json.org/), [XML](https://www.w3.org/XML/) and [CSV](https://www.ietf.org/rfc/rfc4180.txt) are supported out of the box
* Enjoy the **beautiful automatically generated API documentation** (Swagger/[OpenAPI](https://www.openapis.org/))
* Add [**a convenient Material Design administration interface**](https://api-platform.com/docs/admin) built with [React](https://reactjs.org/)
  without writing a line of code
* **Scaffold fully functional Progressive-Web-Apps and mobile apps** built with [React](https://api-platform.com/docs/client-generator/react), [Vue.js](https://api-platform.com/docs/client-generator/vuejs) or [React Native](https://api-platform.com/docs/client-generator/react-native) thanks to [the client
  generator](https://api-platform.com/docs/client-generator) (a Vue.js generator is also available)
* Install a development environment and deploy your project in production using **[Docker](https://api-platform.com/docs/distribution#using-the-official-distribution-recommended)** and [Kubernetes](https://api-platform.com/docs/deployment/kubernetes)
* Easily add **[JSON Web Token](https://api-platform.com/docs/core/jwt) or [OAuth](https://oauth.net/) authentication**
* Create specs and tests with a **developer friendly API testing tool** on top of [Behat](http://behat.org/)
* use **thousands of Symfony bundles and React components** with API Platform
* reuse **all your Symfony and React skills**, benefit of the incredible amount of documentation available
* enjoy the popular [Doctrine ORM](http://www.doctrine-project.org/projects/orm.html) (used by default, but fully optional:
  you can use the data provider you want, including but not limited to MongoDB and ElasticSearch)
  

Credits
-------

Created by [Ruben van der Linde](https://www.conduction.nl/team) for conduction. But based on [api platform](https://api-platform.com) by [Kévin Dunglas](https://dunglas.fr). Commercial support for common ground components available from [Conduction](https://www.conduction.nl).
