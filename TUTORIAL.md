
#

#Best practices

Keeping your repository up to date with the Conduction common ground component 
-------
There are basically three reasons why you should want to keep your repository up to date with the Conduction proto component
* Security, Conduction performs regular security updates on 
* Functionality We strive to make regular 
* Compliance, as discusions in the broader common ground comunity progress API standars might advance or change. Conduction wil regulary update the common ground component with those changes. 

Best practice is to fatch the Conduction common ground component into a local upstream/master branch trough git. Fortunatly there is a simple command for that

```CLI
git fetch upstream
```

You can then use your favorite git tool to merge this branch into your normal working branche without the danger of overwriting your local code. 

For more information on keeping your fork updated take a look at the [github documentation](https://help.github.com/en/articles/syncing-a-fork).


Sharing your work 
-------