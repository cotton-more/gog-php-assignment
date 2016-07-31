GOG PHP Assigmnet
=========

An API to manage catalog and cart

## Install

Clone the repository and install all required libraries by running
    
    composer install
    
Then create an application database (named "gog") and create fixtures 

    ./bin/console doctrine:database:create

    ./bin/console doctrine:schema:update --dump-sql --force

    ./bin/console doctrine:fixtures:load

## Tests

    ./vendor/bin/phpunit
    
## Documentation

Documentation is available at the following url

    /api/doc