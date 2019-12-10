# Installation
This document dives a little bit deeper into installing your component on a kubernetes cluster, looking for information on setting up your component on a local machine? Take a look at the [tutorial](TUTORIAL.md) instead. 

## Setting up helm



## Setting up tiller
Create the tiller service account:

```CLI
$ kubectl -n kube-system create serviceaccount tiller --kubeconfig="api/helm/kubeconfig.yaml"
```

Next, bind the tiller service account to the cluster-admin role:
```CLI
$ kubectl create clusterrolebinding tiller --clusterrole cluster-admin --serviceaccount=kube-system:tiller --kubeconfig="api/helm/kubeconfig.yaml"
```

Now we can run helm init, which installs Tiller on our cluster, along with some local housekeeping tasks such as downloading the stable repo details:
```CLI
$ helm init --service-account tiller --kubeconfig="kubeconfig.yaml"
```

To verify that Tiller is running, list the pods in the kube-system namespace:
```CLI
$ kubectl get pods --namespace kube-system --kubeconfig="kubeconfig.yaml"
```

The Tiller pod name begins with the prefix tiller-deploy-.

Now that we've installed both Helm components, we're ready to use helm to install our first application.


## Setting up ingress
We need at least one nginx controller per kubernetes kluster, doh optionally we could set on up on a per namebase basis

```CLI
$ helm install stable/nginx-ingress --name loadbalancer --kubeconfig="kubeconfig.yaml"
```

We can check that out with 

```CLI
$ kubectl describe ingress pc-dev-ingress -n=kube-system --kubeconfig="kubeconfig.yaml"
```

## Setting up Kubernetes Dashboard
After we installed helm and tiller we can easily use both to install kubernetes dashboard

```CLI
$ helm install stable/kubernetes-dashboard --name dashboard --kubeconfig="kubeconfig.yaml" --namespace="kube-system"
```

But before we can login to tiller we need a token, we can get one of those trough the secrets. Get yourself a secret list by running the following command
```CLI
$ kubectl -n kube-system get secret  --kubeconfig="kubeconfig.yaml"
```

Because we just bound tiller to our admin account and use tiller (trough helm) to manage our code deployment it makes sense to use the tiller token, lets look at the tiller secret (it should look something like "tiller-token-XXXXX" and ask for the corresponding token. 

```CLI
$ kubectl -n kube-system describe secrets tiller-token-xxxxx  --kubeconfig="kubeconfig.yaml"
```

This should return the token, copy it to somewhere save (just the token not the other returned information) and start up a dashboard connection

```CLI
$ kubectl proxy --kubeconfig="kubeconfig.yaml"
```

This should proxy our dashboard to helm making it available trough our favorite browser and a simple link
```CLI
http://localhost:8001/api/v1/namespaces/kube-system/services/https:dashboard-kubernetes-dashboard:https/proxy/#!/login
```


## Cert Manager
https://cert-manager.io/docs/installation/kubernetes/
 
```CLI
$ kubectl apply --validate=false -f https://raw.githubusercontent.com/jetstack/cert-manager/release-0.12/deploy/manifests/00-crds.yaml --kubeconfig="kubeconfig.yaml"
$ kubectl create namespace cert-manager --kubeconfig="kubeconfig.yaml"
```
 
 The we need tp deploy the cert manager to our cluster
 
```CLI
$ helm repo add jetstack https://charts.jetstack.io
$ helm install --name cert-manager --namespace cert-manager --version v0.12.0 \ jetstack/cert-manager --kubeconfig="kubeconfig.yaml"
```

lets check if everything is working

```CLI
$ kubectl get pods --namespace cert-manager --kubeconfig="kubeconfig.yaml"
$ kubectl describe certificate -n dev --kubeconfig="kubeconfig.yaml"
```

## Deploying trough helm
First we always need to update our dependencies
```CLI
$ helm dependency update ./api/helm
```
If you want to create a new instance
```CLI
$ helm install --name pa-dev ./api/helm  --kubeconfig="api/helm/kubeconfig.yaml" --namespace=dev  --set settings.env=dev,settings.debug=1
$ helm install --name pa-stag ./api/helm --kubeconfig="api/helm/kubeconfig.yaml" --namespace=stag --set settings.env=stag,settings.debug=0
$ helm install --name pa-prod ./api/helm --kubeconfig="api/helm/kubeconfig.yaml" --namespace=prod --set settings.env=prod,settings.debug=0
```

Or update if you want to update an existing one
```CLI
$ helm upgrade pa-dev ./api/helm  --kubeconfig="api/helm/kubeconfig.yaml" --namespace=dev  --set settings.env=dev,settings.debug=1
$ helm upgrade pa-stag ./api/helm --kubeconfig="api/helm/kubeconfig.yaml" --namespace=stag --set settings.env=stag,settings.debug=0
$ helm upgrade pa-prod ./api/helm --kubeconfig="api/helm/kubeconfig.yaml" --namespace=prod --set settings.env=prod,settings.debug=0
```

Or del if you want to delete an existing  one
```CLI
$ helm del pa-dev  --purge --kubeconfig="api/helm/kubeconfig.yaml --namespace=dev" 
$ helm del pa-stag --purge --kubeconfig="api/helm/kubeconfig.yaml --namespace=stag" 
$ helm del pa-prod --purge --kubeconfig="api/helm/kubeconfig.yaml --namespace=prod" 
```

Note that you can replace common ground with the namespace that you want to use (normally the name of your component).


## Making your app known on NLX
The proto component comes with an default NLX setup, if you made your own component however you might want to provide it trough the [NLX](https://www.nlx.io/) service. Fortunately the proto component comes with an nice setup for NLX integration.

First of all change the necessary lines in the [.env](.env) file, basically everything under the NLX setup tag. Keep in mind that you wil need to have your component available on an (sub)domain name (a simple IP wont sufice).

To force the re-generation of certificates simply delete the org.crt en org.key in the api/nlx-setup folder.


## Deploying trough common-ground.dev


## Setting up analytics and a help chat function
As a developer you might be interested to know how your application documentation is used, so you can see which parts of your documentation are most read and which parts might need some additional love. You can measure this (and other user interactions) with google tag manager. Just add your google tag id to the .env file (replacing the default) under GOOGLE_TAG_MANAGER_ID. 

Have you seen our sweet support-chat on the documentation page? We didn't build that ourselves ;). We use a Hubspot chat for that, just head over to Hubspot, create an account and enter your Hubspot embed code in het .env file (replacing the default) under HUBSPOT_EMBED_CODE.

Would you like to use a different analytics or chat-tool? Just shoot us a [feature request](https://github.com/ConductionNL/commonground-component/issues/new?assignees=&labels=&template=feature_request.md&title=New%20Analytics%20or%20Chat%20provider)!  
