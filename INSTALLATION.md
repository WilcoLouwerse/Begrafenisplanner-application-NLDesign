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