# Etcd Test

This repo was created to test a few different cases of etcd's locking implementation

## Installing and running etcd
```bash
git clone https://github.com/coreos/etcd.git
cd etcd
./build
./bin/etcd
````

## Installing prerequisites for this package
```bash
composer install
````
Depends on the etcd-php implentation from here:
https://github.com/linkorb/etcd-php
