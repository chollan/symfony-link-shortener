# Link Shortener Application

This application was built to create redirect urls for longer URLs, similar to Bit.ly.

## Schema Location

The schema can be located for this application with in the docker/mysql/schema.sql file.

## `.env` file

Normally with Symfony, `.env` files should be split out into `.env`, `.env.prod`, `.env.dev` and `.env.local`,
yet because of the re-use of the variables in the mysql DNS string, and the order that the `.env` files are
loaded, the application variables were placed directly into the main `.env` file.  

The variables were also done like this for the use of docker compose.

The API keys that are in the file are use with [convert api 3rd party](https://www.convertapi.com/) 
application and use the free account.  If these keys are stolen, a new set of keys can be created under a free account.

## Set up

There are 2 ways to use this site, within a docker container, or within bare metal or even within a VM.

## Bare metal set up

### Clone and `.env` setup

We will need to clone the repository and replace the following .`env` 
file variables with the database that was created for this application:

    DB_USER=unte
    DB_PASS=unte
    DB_NAME=unte
    DB_HOST=database


### Vendor setup

We'll need to install the vendor directory with composer.  To do this, run the following command:

    composer install 

### Database Setup

We'll need to install the database with the console command

    ./bin/console doctrine:schema:create

### Webserver installation

Once the above steps are working, point the web server to the `/public` directory 
and you can browse to the webserver to see the site.

## Docker set up

To run this as a docker, you will need to install [docker and docker compose](https://docs.docker.com/compose/install/).  
Once everything is installed, the simple command `docker-compose up -d` will download and install all necessary docker images and services.

### Vendor setup

Once the containers are running, we'll need to install the vendor the directory.  To do so, run the following commands

    # to ssh into the running container
    docker-compose exec app bash

    # cd to the application directory
    cd /app

    # run composer install
    composer install

### Database Setup

There is no database setup needed here as the database will auto-initialize with the SQL file within docker/mysql/schema.sql

## Questions

If there are any issues or questions about the setup, please feel free to reach out to me at.  
I wish not to put my contact inforamtion here as this is a public github, 
however they can be retrieved from Muhammad Hutasuhut.

    
