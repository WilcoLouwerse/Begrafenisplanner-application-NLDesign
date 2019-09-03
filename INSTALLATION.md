# Installation



## Setting up tiller

## Setting up helm

## Setting up Kubernetes Dashboard
Nadat we helm hebben geinstaleerd kunnen we helm ook meteen gebruiken om gemakkelijke kubernetes dashboard te downloaden
helm install stable/kubernetes-dashboard --name dashboard --kubeconfig="kubernetes/kubeconfig.yaml" --namespace="kube-system"

Maar voordat we op het dashboard kunnen inloggen hebben we eerste een token nodig, die kunnen we ophalen via de secrets 
kubectl -n kube-system get secret  --kubeconfig="kubernetes/kubeconfig.yaml"

Omdat we deployen vanuit helm over tiller is het handig om het dashboard ook als tiller te gebruiken. Kijk naar het tiller secret <tiller-token-XXXXX>, en vraag vervolgens het token daarvoor op met

kubectl -n kube-system describe secrets tiller-token-5m4tg  --kubeconfig="kubernetes/kubeconfig.yaml"

Vanaf hier is het simpel we starten een proxy op
kubectl proxy --kubeconfig="api/helm/kubeconfig.yaml"
En kunnen vervolgens het dashboard aanroepen in onze vavoriete browser met
http://localhost:8001/api/v1/namespaces/kube-system/services/https:dashboard-kubernetes-dashboard:https/proxy/#!/login

## Deploying trough helm

If you want to create a new instance
$ helm install ./api/helm --name commonground --kubeconfig="api/helm/kubeconfig.yaml"

Or update if you want to update an exsisting one
$ helm upgrade commonground  ./api/helm --kubeconfig="api/helm/kubeconfig.yaml" 

Or del if you want to delete an exsisting one
$ helm del commonground  --purge --kubeconfig="api/helm/kubeconfig.yaml" 

Note that you can replace commonground with the namespace that you want to use (normaly the name of your component)


## Deploying trough common-ground.dev


## Setting up analytics and a help chat function
As a developer you might be intrested to now how your application documumentation is used, so that you can see what parts of your documentation are most read and what parts might need some aditional love. You can masure this (and other user interactions) with google tag manager. Just add your google tag id to the .env file (replacing the default) under GOOGLE_TAG_MANAGER_ID. 

Have you seen our sweet suport chat on the documentation page? We didn't build that ourselfs ;) We use a hubspot chat for that, just head over to hubspot create an acount and enter your hubspot embed code in het .env file (replacing the default) under HUBSPOT_EMBED_CODE.

Would you like to use a difrend analytics or chat tool? Just shoot us a [feature request](https://github.com/ConductionNL/commonground-component/issues/new?assignees=&labels=&template=feature_request.md&title=New Analytics or Chat provider)  
