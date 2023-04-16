# DEMO Cart Api 

This demo provides rudimentary access to carts, line items (products in carts) and products.

## API Documentation

There are two Documentations
* YAML OpenAPI Documentation can be found under repository root `openapi.yaml`
* HTML Documentation generated with redocly from the OpenAPI Documentation can be found under repository root `openapi.html` 

## Running the Service

The Repository comes with a Docker compose configuration which works out of the box with the .env configuration.
The default configuration comes with xDebug installed.

### Initial Run

* Start the Containers with `docker-compose up -d`
  * This will donwload all images and execute the additional build steps like installing xDebug
* Create the DB Tables
  * Execute the following commands to get a working Database setup
    * `docker-compose exec php bin/console doctrine:migrations:migrate`
    * `docker-compose exec php bin/console doctrine:fixtures:load`
      * With that you've three dummy products in the Database and you're ready to create carts and line items
* Make API Requests
  * The File `manual_testing.http` in the Repository Root can be used to execute the HTTP Requests. They have the use case as comment above. 
  * The *.http files can e.g. be used with PHPStorm