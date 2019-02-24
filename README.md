# wp-menu-item-search-form

Add a search-form to menus


## Filters

#### sticky_widget_area_options

Filter options passed to the js implementation.
See [sticky-sidebar](https://www.npmjs.com/package/sticky-sidebar) for more information.


## Development

Download [Docker CE](https://www.docker.com/get-docker) for your OS.
Download [NodeJS](https://nodejs.org) for your OS.

### Install

#### Install wordpress

```cli
docker-compose run --rm wp-cli install-wp
```

After installation you can log in with user `wordpress` and password `wordpress`.

#### Install front-end dependencies

```cli
npm i
```

### Development Server

Point terminal to your project root and start up the container.

```cli
docker-compose up -d
```

Point your browser to [http://localhost:8030](http://localhost:8030).


#### Watch front-end dependencies

```cli
npm run watch
```

### Docker

##### Update composer dependencies

```cli
docker-compose run composer update
```

##### Globally stop all running docker containers

```cli
docker stop $(docker ps -a -q)
```

## Production

Create a build for production

```cli
npm run build
```
