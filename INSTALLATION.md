# Installation
This document dives a little bit deeper into installing your component on a kubernetes cluster, looking for information on setting up your component on a local machine? Take a look at the [tutorial](TUTORIAL.md) instead. 

## Setting up helm
We first need to be sure the stable repository of helm and kubernetes is added. We do this using the following command:
```CLI
$ helm repo list
```

If in the output there is no repository 'stable' we need to add it:

```CLI
$ helm repo add stable https://kubernetes-charts.storage.googleapis.com
```

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
$ kubectl apply -f https://raw.githubusercontent.com/kubernetes/dashboard/v2.0.0/aio/deploy/recommended.yaml --kubeconfig=kubeconfig.yaml
```

But before we can login to tiller we need a token, we can get one of those trough the secrets. Get yourself a secret list by running the following command
```CLI
$ kubectl -n kube-system get secret  --kubeconfig="kubeconfig.yaml"
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
$ kubectl create namespace cert-manager --kubeconfig="kubeconfig.yaml"
```
 
 The we need tp deploy the cert manager to our cluster
 
```CLI
$ helm repo add jetstack https://charts.jetstack.io
$ helm install cert-manager --namespace cert-manager --version v0.15.0 jetstack/cert-manager --set installCRDS=true --kubeconfig="kubeconfig.yaml"
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
$ helm install --name pc-dev ./api/helm  --kubeconfig="api/helm/kubeconfig-digi.yaml" --namespace=dev  --set settings.env=dev,settings.debug=1
$ helm install --name pc-stag ./api/helm --kubeconfig="api/helm/kubeconfig-digi.yaml" --namespace=stag --set settings.env=stag,settings.debug=0
$ helm install --name pc-prod ./api/helm --kubeconfig="api/helm/kubeconfig-digi.yaml" --namespace=prod --set settings.env=prod,settings.debug=0
```

Or update if you want to update an existing one
```CLI
$ helm upgrade pc-dev ./api/helm  --kubeconfig="api/helm/kubeconfig-digi.yaml" --namespace=dev  --set settings.env=dev,settings.debug=1
$ helm upgrade pc-stag ./api/helm --kubeconfig="api/helm/kubeconfig-digi.yaml" --namespace=stag --set settings.env=stag,settings.debug=0
$ helm upgrade pc-prod ./api/helm --kubeconfig="api/helm/kubeconfig-digi.yaml" --namespace=prod --set settings.env=prod,settings.debug=0
```

Or del if you want to delete an existing  one
```CLI
$ helm del pc-dev  --purge --kubeconfig="api/helm/kubeconfig-digi.yaml" 
$ helm del pc-stag --purge --kubeconfig="api/helm/kubeconfig-digi.yaml" 
$ helm del pc-prod --purge --kubeconfig="api/helm/kubeconfig-digi.yaml" 
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
