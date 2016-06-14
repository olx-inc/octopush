# Feeds environment for development

## Dependencies
* [Docker](https://docs.docker.com/engine/installation/)
* [Docker compose](https://docs.docker.com/compose/install/)

# Setup

* The first time you must build images:

```
$ docker build
```

## Run environment
```
$ docker-compose up -d
```

## Access

* API: http://feeds.local (map host to 127.0.0.1) or http://feeds.127.0.0.1.xip.io
* Grafana: http://graphite.local (map host to 127.0.0.1) http://graphite.127.0.0.1.xip.io
* Mysql: connect to port 3306 as usual to create the schema

## Feeds configuration

* Mysql:
 * host: mysql
 * port: 3306
 * database: DBOLX_FEEDS
 * user: root
 * password: verysecret
* Memcached:
 * host: memcached
 * port: 11211
* Graphite:
 * host: grahite
 * port: 2003
 * namespace: latam.local.aws.aplications.feeds

## Run project command
```
$ ./run-command <command-name>
```

## Tail logs
```
$ docker logs -f feedsenvironment_feeds_1
```
