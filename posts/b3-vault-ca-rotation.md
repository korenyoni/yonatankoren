# Vault certificate rotation for Kubernetes

## Bootstrap K8s/Etcd certificates:

1. Create CA for etcd
2. Create CA for K8s

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
    * Note: Tokens are by default renewable in Vault

## Control Loop:

[!Vault ca rotation](/images/cert-rotation.png)

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

We use the Salt vault-token beacon to check the token expiry very hour.
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