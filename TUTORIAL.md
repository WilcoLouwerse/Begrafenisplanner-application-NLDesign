# Tutorial

What do you need for this tutorial?
* Browser
* Github account
* Git client
* Docker account
* Docker for desktop

## Before you begin
For the steps considering the generation of resources (or entities as symfony calls them) an example resource a availale, feel free to [take a look](https://github.com/ConductionNL/Proto-component-commonground/blob/master/api/src/Entity/ExampleEntity.php) at it if you have trouble figuring out the code.

## Setting up your enviroment

You can install docker-desktop from [the docker website](https://hub.docker.com/editions/community/docker-ce-desktop-windows). 


## Generating your component (repository/codebase)
Starting up your first Common Ground component is extremely easy, al you need is a GitHub account and go the link below and fill in the form, press create and press to we have a component!

[https://github.com/ConductionNL/Proto-component-commonground/generate](https://github.com/ConductionNL/Proto-component-commonground/generate) 

After that you should be redirected to your own brand new repository. 

**Oke cool but what did we just do?**
We ran a fork of the base Common Ground component, that means that we copied the code of the original project into a new repository. By doing so we made sure we have all the necessities for our component, including security and compliance with international standards. 

## Spinning up your component
Before we can spin up our component we must first get a local copy from our repository, we can either do this through the command line  or use a Git client. 

For this example we're going to use GitKraken but you can use any tool you like, feel free to skip this part if you are already familiar with setting up a local clone of your repository.

Open gitkraken press "clone a repo" and fill in the form (select where on your local machine you want the repository to be stored, and fill in the link of your repository on github), press "clone a repo" and you should then see GitKraken downloading your code. After it's done press "open now" (in the box on top) and voilá your codebase (you should see an initial commit on a master branch).

You can now navigate to the folder where you just installed your code, it should contain some folders and files and generally look like this. We will get into the files later, lets first spin up our component!

Open a command window (example) and browse to the folder where you just stuffed your code, navigating in a command window is done by cd, so for our example we could type 
cd c:\repos\common-ground\my-component (if you installed your code on a different disk then where the cmd window opens first type <diskname>: for example D: and hit enter to go to that disk, D in this case). We are now in our folder, so let's go! Type docker-compose up and hit enter. From now on whenever we describe a command line command we will document it as follows (the $ isn't actually typed but represents your folder structure):

```CLI
$ docker-compose up
```

Your computer should now start up your local development environment. Don't worry about al the code coming by, let's just wait until it finishes. You're free to watch along and see what exactly docker is doing, you will know when it's finished when it tells you that it is ready to handle connections. 

Open your browser type <http://localhost/> as address and hit enter, you should now see your common ground component up and running.

### Trouble shooting
When spinning up components we make extensive use of the cashing of docker, and use volumes to represent server disks. When running in to unexpected trouble always remember to clear your local docker vm with the -a command (removing image cash)

```CLI
$ docker system prune -a
```
```CLI
$ docker volume prune
```

**What are we looking at?**
The Common Ground base component provides a bit more than just a development interface, it also includes an example application and a backend that automatically hooks into your api. For now we're just going to focus on our api, but is good to read up on all the features of the Common Ground base component here.  

## Adding your own resources
You can now access your api at http://localhost:8080/, as you can see it's pre-loaded with some example resources. Let's replace them with your own resources!

First let's remove the resources currently in the api, we can do that by just removing the resources form our code base, navigate to the folder where you stored your code and open the folder api/src/Entity , you can find the example entities (the symfony name for resources) there. Just delete all the php files in that folder.

Next let's add our own resources, we can do this in two ways, we can do old fashioned coding, but we can also use the build in maker bundle of the proto component, to quickly generate our entities for us (without the fuss of actual coding).
 
Let's open a new command line window and navigate to our root folder, exactly like we did under "spinning up your component". And then lets fire up maker bundle (make sure that your component is still running in your other command window). We can do so by the following command:

```CLI
$ docker-compose exec php bin/console make:entity
```
We should now see a wizard that allows us to either make new entities, or add parameters to existing entities (by supplying the name of an existing resource). 

## Keeping your repository up to date with the Conduction Common Ground component 

There are basically three reasons why you should want to keep your repository up to date with the Conduction proto component:

* Security, Conduction performs regular security updates on 
* Functionality we strive to make regular 
* Compliance, as discussions in the broader Common Ground community progress API standards might advance or change. Conduction will regularly update the Common Ground component with those changes. 

Best practice is to fetch the Conduction Common Ground component into a local upstream/master branch through Git. So let's first add the original Common Ground component as an remote called upstream, and create a local branch for that remote.  

__Please make sure the you have committed al your changes to your current codebase and pushed a backup copy to your Git repo before continuing__

```CLI
git remote add upstream https://github.com/ConductionNL/Proto-component-commonground.git
git fetch upstream
git branch upstream upstream/master
```

You can then use your favorite Git tool to merge this branch into your normal working branch without the danger of overwriting your local code. Or alternatively you can use your GIT CLI (not  recommended)

```CLI
git checkout master
git pull upstream master --allow-unrelated-histories
```

You might get an error at this point in the lines of 'refusing to merge unrelated histories', that basically means that you lost your history connection with the original repository. This can happen for several reasons, but is easily fixable.

```CLI
git checkout upstream
git pull upstream master --allow-unrelated-histories
git checkout master
git merge upstream --allow-unrelated-histories
``` 

Keep in mind that you wil need to make sure to stay up to date about changes on the Common Ground component repository.

## Renaming your component
Right now the name of your component is `commonground component` and its unique id `cg` that's that's fine while running it locally or in its own kubernetes cluster but wil get you in when running it with other components when it without using a name space. So its good practice to name your component distinctly. But besides al of these practical reasons its of course also just cool to name your child before you unleash it on the unsuspecting common ground community.

Oke, so before we can nae the component we need to come up with a name. There are a couple of conventions here. First of the name should tell us what the component does, or is supposed to do with one or two words. So we would normally call an component about dogs the DogComponent and one about cats te CatComponent. The second convention is that we don't usually actually name our component 'component' but indicate its position in de common ground architecture. For that we have the following options:
* Catalogus
* RegistratieComponent
* Service
* Application
* Tool

The actual name change is rather simple doh, just head over to the .env that contains all our config and change the apropriate variables
* .env

## Setting up security and access 
We want to secure our resources in such a way that only users or applications with propper right can acces and update properties. 

```PHP
// src/Entity/Organisation.php
namespace App\Entity;

// ...
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}},
 *     denormalizationContext={"groups"={"write"}}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 */
class Organisation
{
    /**
     * @Groups({"read","write"})
     */
    private $name;
}
```

## Using validation
Right now we are just accepting data and passing them on to the database, and in a mock or poc context this is fine. Most of the calls will end up being get requests anyway. But in case that we actually want our clients to make post to the api it would be wise to add some validation to the fields we are recieving. Luckely for us the component comes pre packed with a valdiation tool that we can configure from our resources through annotion. If we for example want to make a field required we could do so as follows: 

```PHP
// src/Entity/Organisation.php
namespace App\Entity;

// ...
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\OrganisationRepository")
 */
class Organisation
{
    /**
     * @Assert\NotBlank
     */
    private $name;
}
```

Keep in mind that we need to add the assert annotation to our class dependencies under 'use'.  

More information on using validation can be found at the [symfony website](https://symfony.com/doc/current/validation.html), but it is als worth nothing that tis component comes pre packed with some typical NL validators like BSN. You can find those [here]().

## Using UUID
As default doctrine uses auto increment integers as identifiers (1,2, etc). For modern web applications we however prefer the use of UUID's. (e.g. e2984465-190a-4562-829e-a8cca81aa35d). Why? Wel for one it is more secure integer id's are easily guessable and make it possible to "ask" endpoint about resources that you should not know about. But UUID's also have a benefit in future proofing the application. If we in the future want to merge a table with another table (for example because two organisations using a component perform a merger) then we would have to reassign al id's and relations if we where using int based id's (both tables would have a row 1,2 etc) with UUID's however the change of doubles range somewhere in the billions. Meaning that it is likely that we only need to either reidentify only a handful of rows or more likely none at al! Turning our entire migration into a copy paste action.

The proto component supports Ramsey's uuid objects strategy out of the box, so to use UUID's as identifier simply we need to add the ApiProperty as a dependency


```PHP
//...
use Symfony\Component\Serializer\Annotation\Groups;
//...
```
and  replace the default id property

```PHP
//...
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
//...
```
with

```PHP
//...
	/** 
	 * @var \Ramsey\Uuid\UuidInterface The UUID identifier of this resource
	 * @example e2984465-190a-4562-829e-a8cca81aa35d
	 *
	 * @Groups({"read"})
	 * @ORM\Id
	 * @ORM\Column(type="uuid", unique=true)
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
	 */
	private $id;
//..	
```

and remove the : ?integer on the getter turning this:

```PHP
//...
    public function getId(): ?integer
    {
        return $this->id;
    }
//...
```

into this

```PHP
//...
    public function getId()
    {
        return $this->id;
    }
//...
```

and you're all done

### Trouble shooting
If you have already spun your component including your new resource your going to run into some trouble because doctrine is going to try changing your primary key column (id) from an integer to string (tables tend not to like that). In that case its best to just drop your database and reinstall it using the following commands:

```CLI
$ bin/console doctrine:schema:drop
$ bin/console doctrine:schema:update --force
```

## Advanced data sets

Oke lets make it complex, until now we have just added some simple entities to our component, but what if we want to attaches one resource to another? Fortunately our build in database engine support rather complex scenarios called associations. So let [take a look](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/association-mapping.html) at that.  

Baffled? Wel its rather complex. But remember that make:entity command that we used earlier? That actually accepts relations as a data type. Or to but it simply instead of using the default 'string' we could just type "ManyToOne" and it will just fire up some questions that will help it determine how you want your relations to be.


### Trouble shooting
A very common error when linking entities together is circle references, those will break our serialization. Fortunately we have a need way to prevent that. Even better symfony gives us exact control of how deep we want the circular reference to go. To do this we need to use the `MaxDepth()` annotation. So lets import that 

```PHP
//...
use Symfony\Component\Serializer\Annotation\MaxDepth;
//...
```

And tell our serializer to use it.

```PHP
//...
/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ExampleEntityRepository")
 */
class ExampleEntity
	{
//...
```

We can now prevent circular references by setting a max depth on the properties causing the circular reference.
```PHP
//...
    /**
     * @var ArrayCollection $stuffs Some stuff that is attached to this example resource
     * 
     * @MaxDepth(1)
     * @Groups({"read","write"})
     * @ORM\ManyToOne(targetEntity="App\Entity\Stuff", inversedBy="examples")
     */
    private $stuffs;     
//...
```

## Data fixtures
For testing cases it can be useful to use data fixtures a predefined set of data that fills the database of your component at startup. Since we use php classes to describe our resources creating fixtures is easy (you can find an example in your project folder at api/src/DataFixtures). We simply go trough some classes assign values and persist them to the database. Once we have written our fixtures we can use a single command to load them  

```CLI
$ bin/console doctrine:fixtures:load --env=dev
```

Be mindful of the --env=dev here! Doctrine wil only allow fixture loading on a dev environment (for obvious security reasons)

More information on using data fixtures can be found at the [symfony website](https://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html) (you can skipp the installation instructions) we also encourage you to take a look at the [tabellen component](https://github.com/ConductionNL/landelijketabellencatalogus) that makes extensive use of data fixtures.

## Sharing your work 
A vital part of te common ground community is sharing your work, and telling other people what you are working. This way people can help you with problems that you run into. And keep tabs on any (security) updates that you make to you code. Sounds like a lot of work right?

Wel it actually isn't, there is a specific common ground platform over at common-ground.dev that reads repositories and updates user. So the only thing we need to do is tell this platform that we have started a new common ground repository. How do we do that? Simple we use the name common ground (or commonground) in the discription of our repository. common-ground.dev should then pick up our repository within the hour.

Another option that we have is to declare our repository on [publiccode](), to do this you need to copy the publiccode.yaml from the [api/public/schema](api/public/schema]) folder to your root folder (dont forget to redo this every time you make a major change to your repository concerning versioning or licencing).  


Continues integration
-------
> The following bit of the tutorial requires an additional account
> - [https://hub.docker.com/](https://hub.docker.com/) (You might already have this for docker for desktop)

The proto component ships with a pre-fab continues integration script based on github action (there is also a travis script in here if you want it). What does this mean you ask? Continuous integration (or CI for short) is an optimized and automated way for your code to become part of your projects. In the case of your commonground component that means that we will automatically validate new code commits or pushes and (if everything checks out) build that code and deploy the containers thereof to docker hub. Making is possible to update al the environments that use those components. Whats even better is that we check your code for known security issues, so whenever a dependency or libary has a security issue you will be notified to take action.

Okay, that's nice, but how do we do that? Actually it is very simple. You do nothing. The scripts are already enabled by default. Just go to the actions tab of your github repository to see the results whenever you push code.

There is however a bit of extra here that you can do and that is to insert your docker hub credentials into the repository. You can do that under the settings->secrets tab of yout repoistory by setting a `DOCKERHUB_USERNAME` and `DOCKERHUB_PASSWORD` secret containing (you might have guesed it) your dockerhub username and secret. And all done! Head over back to the code on your computer and make a small change. Then commit push that change into github. Wait for the action to complete and head over to your docker hub repository page. You should find your build containers ready for you.

Continues deployment
-------
> The following bit of the tutorial requires an additional account
> - [https://www.digitalocean.com/](https://www.digitalocean.com/) 

Actually the repository goes a bit further then just getting your containers ready to deploy, it can acctually deploy them for you! Again all the code is already there. The only thing that you need to do is add a kubeconfig file. You can get a kubeconfig file from a running kubernetes clusters, it provides your repository with both the credentials and endpoints it needs to deploy the application. How you get a Kubeconfig file difers a bit from provider to provider. But you can get more information on that here

- [Digitalocean](https://www.digitalocean.com/docs/kubernetes/how-to/connect-to-cluster/)
- [Google Cloud](https://cloud.google.com/sdk/gcloud/reference/container/clusters/get-credentials)
- [Amazone AWS](https://docs.aws.amazon.com/eks/latest/userguide/create-kubeconfig.html)

Afther you have abtained a kuneconfig you need to save it to your repository as a secret (NEVER COMMIT A KUBECONFIG FILE), use the secret `KUBECONFIG` to save your cubeconfig file. Now simply commit and push your code to your repository and presto! You have a working common-ground component online.

Documentation and dockblocks
-------
You want both your redoc documentation and your code to be readable and reausable to other developers. To this effect we use docblok annotation. You can read more about that [here](https://docs.phpdoc.org/references/phpdoc/basic-syntax.html) but the basic is this, we supply each class and propery with a docblock contained within /\* \* / characters. At the very least we want to describe our properties, the expected results and example data (see the example under [audittrail](#audittrail)

You can generate documantation trough docker-compose exec php php phpDocumentor.phar -d src -t public/docs

### Adjusting your readme file 

### Using docblocks for in code documentation

### Setting up you Read the Docs page

### Setting up github pages

### Exposing your API documentation

Audittrail
-------
As you might expect the proto-component ships with a neat function for generating audit trails, that basically exist of three parts. 

First we need to activate logging on the entities that we want logged (for obvious security reasons we don't log resource changes by default) to do that by adding the `@Gedmo\Loggable` annotation to our php class, which should then look something like:

```PHP
//...
/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ExampleEntityRepository")
 * @Gedmo\Loggable
 */
class ExampleEntity
	{
//...
```

Next we need to tell the specific properties that we want to log that they are loggable (again this is a conscious choice, to prevent us from accidentally logging stuff like bsn numbers), we do that by adding the `@Gedmo\Versioned` annotation to those specific properties. That would then look something like this:

```PHP
//...
    /**
	 * @var string $name The name of this example property
	 * @example My Group
	 * 
	 * @Assert\NotNull
	 * @Assert\Length(
	 *      max = 255
	 * )
     * @Gedmo\Versioned
	 * @Groups({"read","write"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;
//...
```

Okay actually we are now good to go, at least we are logging those things that we want logged. But.... How do we view those logs? In common ground we have a [convention](https://zaakgerichtwerken.vng.cloud/themas/achtergronddocumentatie/audit-trail) to expose a /audittrail subresource on resources that are logged. So lets add that trough our `@ApiResource` annotation.

```PHP
//...
/**
 * @ApiResource(
 *     normalizationContext={"groups"={"read"}, "enable_max_depth"=true},
 *     denormalizationContext={"groups"={"write"}, "enable_max_depth"=true}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\ExampleEntityRepository")
 * @Gedmo\Loggable
 */
class ExampleEntity
	{
//...
```

And now we have a fully nl api strategy integrated audit trail!
