# Vault certificate rotation for Kubernetes

The kubernetes components should not be able to communicate directly with the etcd servers. The k8s components can communicate with each other, but the k8s apiserver acts as a bridge between etcd and the k8s components.
Therefore we create a CA for etcd and a CA for the K8s components.

## Bootstrap K8s/Etcd certificates:

1. Create CA for etcd
2. Create CA for K8s components

3. Create roles for etcd:
    * etcd

4. Create roles for K8s components
    * kubelet
    * kube-scheduler
    * kube-controller-manager
    * kubectl

5. Create service account key for K8s components:

```
openssl genrsa 4096 > token-key
vault write secret/$CLUSTER_ID/k8s/token key=@token-key
rm token-key
```

6. Create policy for etcd-members to generate new intermediate certs in vault

7. Create policy for kube-apiserver to generate new intermediate certificates in vault and read the k8s service account key

8. Provide each machine with a token for its respective role
    * Note: tokens are by default renewable in Vault

## Putting Vault into etcd

Because we want to use the etcd backend in vault to store vault data in etcd, we need start etcd and then the vault server with the etcd backend.
However, we need the vault-generated certificate to start etcd, therefore we are left with a chicken and egg problem.

We have the option of using a different way to issue certificates to etcd, for example cfssl. We also have the option of keeping etcd unsecured while
the cluster does not accept any traffic.

## Control Loop:

![Vault ca rotation](/images/cert-rotation.png)

Each machine is polled every hour for its token expiry and certificate expiry.

## Token expiry beacon and certificate expiry beacon

Develop in an [Salt-Vagrant environment](https://github.com/UtahDave/salt-vagrant-demo) then put into `/srv/salt/_beacons`.
Although these beacons will use python libraries, these sh commands can help us understand the overall idea:

We can check if a token expires in the next 1:59h using the following command:
```
$ curl \
    --header "X-Vault-Token: ..." \
    --request GET \
    https://vault.server:8200/v1/auth/token/lookup-self | python -c 'import json,sys;obj=json.load(sys.stdin);print int(obj["data"]["ttl"]) >= 7140'
```

We can also check if a certificate expires in the next 1:59h using the following command:
```
$ openssl x509 -checkend 7140 -noout -in file.crt
```

## Renewing a Vault token before expiry

We use the Salt vault-token beacon to check the token expiry every hour.
If the token expires within the next 1:59, we renew it with an increment of 30 days, minus an hour:

```
$ cat <<EOF > payload.json
{
  "token": ...,
  "increment": "43140"
}
EOF
$ curl \
    --header "X-Vault-Token: ..." \
    --request POST \
    --data @payload.json \
    https://vault.server/v1/auth/token/renew
```

## Renewing a certificate before expiry

We use the Salt certificate beacon to check the certificate expiry every hour.
If the certificate expires within the next 1:59, we request a new one:

```
$ cat <<EOF > payload.json
{
  "common_name": internal.etcd
}
EOF
$ curl \
    --header "X-Vault-Token: ..." \
    --request POST \
    --data @payload.json \
    https://vault.server/v1/pki/issue/etcd
```

# References:
* [DigitalOcean: Kubernetes certificates using Vault](https://blog.digitalocean.com/vault-and-kubernetes/)
* Vault docs
* [Securing etcd clusters](https://continuously.me/2016/09/10/vault-secure-etcd-k8s/)
* [Enabling HTTPS in an existing etcd cluster](https://continuously.me/2016/09/10/vault-secure-etcd-k8s/)
