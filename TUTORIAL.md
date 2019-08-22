#Tutorial

Keeping your repository up to date with the Conduction common ground component 
-------
There are basically three reasons why you should want to keep your repository up to date with the Conduction proto component
* Security, Conduction performs regular security updates on 
* Functionality We strive to make regular 
* Compliance, as discusions in the broader common ground comunity progress API standars might advance or change. Conduction wil regulary update the common ground component with those changes. 



Best practice is to fatch the Conduction common ground component into a local upstream/master branch trough git. So lets first add the original common ground component as an remote called upstream, and create a local branch for that remote. 

```CLI
git remote add upstream https://github.com/ConductionNL/commonground-component.git
git branch upstream upstream/master
```

Now we are ready to merge any updated code into our local repository, lets start with pulling the latest code for upstream

```CLI
git fetch upstream
```

You can then use your favorite git tool to merge this branch into your normal working branche without the danger of overwriting your local code. Or alternativly you can use your GIT CLI (not  recomended)

```CLI
git checkout master
git merge upstream
```

Keep in mind that you wil need to make sure to stay up to date about changes on the common ground component repository 


Sharing your work 
-------