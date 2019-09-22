# Tutorial

For this tutorial you need the following:

* Browser
* Github account
* Git client
* Docker account
* Docker for desktop


## Generating your component (repository/codebase)
Starting up your first Common Ground component is extremely easy, al you need is a GitHub account and go the link below and fill in the form, press create and press to we have a component!

[https://github.com/ConductionNL/Proto-component-commonground/generate](https://github.com/ConductionNL/Proto-component-commonground/generate)

After that you should be redirected to your own brand new repository. 

**Oke cool but what did we just do?**
We ran a fork of the base Common Ground component, that means that we copied the code of the original project into a new repository. By doing so we made sure we have all the necessities for our component, including security and compliance with international standards. 

## Spinning up your component
Before we can spin up our component we must first get a local copy from our repository, we can either do this through the command line (example here) or use a Git client. 

For this example where going to use GitKraken but you can use any tool you like, feel free to skip this part if you are already familiar with setting up a local clone of your repository.

Open gitkraken press *“clone a repro”* and fill in the form (select where on your local machine you want the repository to be stored, and fill in the link of your repository on github), press *“clone a repro!”* and you should then see GitKraken downloading your code. After it's done press ‘open now’ (in the box on top) and voilá your codebase (you should see an initial commit on a master branche).

You can now navigate to the folder where you just installed your code, it should contain some folders and files and generally look like this. We will get into the files later, lets first spin up our component!

Open a command window (example) and browse to the folder where you just stuffed your code, navigating in a command window is done by cd, so for our example we could type 
cd c:\repos\common-ground\my-component (if you installed your code on a different disk then where the cmd window opens first type <diskname>: for example D: and hit enter to go to that disk, D in this case). We are now in our folder, so let's go! Type docker-compose up and hit enter. From now on whenever we describe a command line command we will document it as follows:

```CLI
$ docker-compose up
```

Your computer should now start up your local development environment. Don’t worry about al the code coming by, let's just wait until it finishes. You're free to watch along and see what exactly docker is doing, you will know when it's finished when it tells you that it is ready to handle connections. 

Open your browser type <http://localhost/> as address and hit enter, you should now see your common ground component up and running.

**What are we looking at?**
The Common Ground base component provides a bit more than just a development interface, it also includes an example application and a backend that automatically hooks into your api. For now we're just going to focus on our api, but is good to read up on all the features of the Common Ground base component here.  

## Adding your own objects
You can now access your api at <http://localhost:8080/>, as you can see it's pre-loaded with some example objects. Let's replace them with your own objects!

First let's remove the objects currently in the api, we can do that by just removing the entities form our code base, navigate to the folder where you stored your code and open the folder api/src/Entity , you can find the example entities (our name for objects) there. Just delete all the php files in that folder.

Next let's add our own entities, we can do this in two ways, we can do old fashioned coding, but we can also use the build in maker bundle of the proto component, to quickly generate our entities for us (without the fuss of actual coding).
 
Let's open a new command line window and navigate to our root folder, exactly like we did under *“spinning up your component”*. And then lets fire up maker bundle (make sure that your component is still running in your other command window). We can do so by the following command:

```CLI
$ docker-compose exec php php bin/console make:entity --api-platform
```
We should now see a wizward that allows us to either make new entities, or add parameters to existing entities (by supplying the name of an existing entity). 

## Keeping your repository up to date with the Conduction Common Ground component 

There are basically three reasons why you should want to keep your repository up to date with the Conduction proto component:

* Security, Conduction performs regular security updates on 
* Functionality we strive to make regular 
* Compliance, as discussions in the broader Common Ground community progress API standars might advance or change. Conduction will regularly update the Common Ground component with those changes. 

Best practice is to fetch the Conduction Common Ground component into a local upstream/master branch through Git. So let's first add the original Common Ground component as an remote called upstream, and create a local branch for that remote.  

__Please make sure the you have commited al your changes to your current codebase and pushed a backup copy to your Git repo before continuing__

```CLI
git remote add upstream <https://github.com/ConductionNL/Proto-component-commonground.git>
git fetch upstream
git branch upstream upstream/master
```

You can then use your favorite Git tool to merge this branch into your normal working branche without the danger of overwriting your local code. Or alternatively you can use your GIT CLI (not  recommended)

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

## Sharing your work 
A vital part of te common ground community is sharing your work, and telling other people what you are working. This way people can help you wiht problems that you run into. And keep tabs on any (security) updates that you make to you code. Sounds like a lot of work right?

Wel it actually isn't, there is a specific commonground platform over at common-gorund.dev that reads repositorys and updates user. So the only thing we need to do is tell this platform that we have started a new common ground repository. And tell it when we have updates ours. We can do all that by simply adding a webhook to our component. 

When using Github. To set up a webhook, go to the settings page of your repository or organization. From there, click Webhooks, then Add webhook. Use te following settings:

* Payload URL: <https://www.common-ground.dev/webhook/github>
* Content type: Application/JSON
* Secret: [leave blanck]
* Events: [just the push event]

Now every time you update your repository the commonground dev page will allerted, rescan your repository and do al the apropriate platform actions. It just as easy as that.




## Commonground specific data types


### incompleteDate

### underInvestigation

