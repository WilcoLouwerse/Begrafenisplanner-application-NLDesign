# Tutorial

What do you need for this tutorial?
* browser
* github acount
* git client
* docker acount
* docker for desktop

## Generating your component (repository/codebase)
Starting up your first common ground component is extremely easy, al you need is a GitHub account and go the link below and fill in the form press create and presto we have a component!

[https://github.com/ConductionNL/commonground-component/generate](https://github.com/ConductionNL/commonground-component/generate)

After that you should be redirected to your own brand new repository 

**Oke cool but what did we just do?**
We ran a fork of the base common ground component, that means that we copied the code of the original project into a new repository. By doing so we made sure we have all the necessities for our component including security and compliance with international standards. 

## Spinning up your component
Before we can spin up our component we must first get a local copy from our repository, we can either do this through the command line (example here) or use a git client. 

For this example where going to use GitKraken but you can use any tool you like, feel free to skip this part if you are already familiar with setting up a local clone of your repository.

Open gitkraken press �clone a repro� and fill in the form (select where on your local machine you want the repository to be stored, and fill in the link of your repository on github), press �clone the repro!� and you should then see GitKraken downloading your code. Afther it is doen presse �open now� (in the box on top) and voil� your codebase (you should see an initial commit on a master branche).

You can now navigate to the folder where you just installed your code, it should contain some folders and files and generally look like this. We will get into the files later, lets first spinn up our component!

Open a command window (example) and browse to the folder where you just stuffed your code, navigating in a command window is done by cd, so for our example we could type 
cd c:\repos\common-ground\my-component (if you installed your code on a different disk then where the cmd window opens first type <diskname>: for example D: and hit enter to go to that disk, D in this case). We are now in our folder so lets go! Type docker-compose up and hit enter. From now on whenever we describe a command line command we wil document it a follows.

```CLI
$ docker-compose up
```

Your computer should now start up your local development environment. Don�t worry about al the code coming by, lets just wait until it finishes. Your free to watch along and see what exactly docker is doing but you will know when its finished when it tells you that it is ready to handle connections. 

Open your browser type http://localhost/ as address and hit enter, you should now see your common ground component up and running.

**What are we looking at?**
The common ground base component provides a bit more then just a development interface, it also includes an example application and a backend that automatically hooks into your api. For now where just going to focus on our api but is good to read up on all the features of the common ground base component here.  

## Adding your own objects
You can now access your api at http://localhost:8080/, as you can see its pre-loaded with some example objects. Lets replace them with your own objects!

First lets remove the objects currently in the api, we can do that by just removing the entities form our code base, navigate to the folder where you stored your code and open the folder api/src/Entity you can find the example entities (our name for objects) there. Just delete all the php files in that folder.

Next let's add our own entities, we can do this in two way we can do old fashioned coding but we can also use the build in maker bundle of the propto component to quickly generate our entities for us (without the fuss of actual coding).
 
Lets open a new command line window and navigate to our root folder, exactly like we did under �spinning up your component�. And then lets fire up maker bundle (make sure that your component is still running in your other command window). We can do so by the following command:

```CLI
$ docker-compose exec php php bin/console make:entity --api-platform
```
We should now see a wizward that allows us to either make new entities, or add parameters to existing entities (by supplying the name of an existing entity). 

## Keeping your repository up to date with the Conduction common ground component 

There are basically three reasons why you should want to keep your repository up to date with the Conduction proto component
* Security, Conduction performs regular security updates on 
* Functionality We strive to make regular 
* Compliance, as discusions in the broader common ground comunity progress API standars might advance or change. Conduction wil regulary update the common ground component with those changes. 

Best practice is to fatch the Conduction common ground component into a local upstream/master branch trough git. So lets first add the original common ground component as an remote called upstream, and create a local branch for that remote.  

__Please make sure the you have commited al your changes to your current codebase and pushed a backup copy to your gitrepro before continuing__

```CLI
git remote add upstream https://github.com/ConductionNL/commonground-component.git
git fetch upstream
git branch upstream upstream/master
```

You can then use your favorite git tool to merge this branch into your normal working branche without the danger of overwriting your local code. Or alternativly you can use your GIT CLI (not  recomended)

```CLI
git checkout master
git pull upstream master --allow-unrelated-histories
```

You might get an error at this point in the lines of 'refusing to merge unrelated histories', that basicaly means that you lost your history connection with the original repository. This can happen for several reasons but is easaly fixable.

```CLI
git checkout upstream
git pull upstream master --allow-unrelated-histories
git checkout master
git merge upstream --allow-unrelated-histories
``` 

Keep in mind that you wil need to make sure to stay up to date about changes on the common ground component repository 

## Sharing your work 


## Commonground specific data types


### incompleteDate

### underInvestigation

