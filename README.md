# Dorak Test Project



Test Project with api for tagging images

----------

## Installation



Clone the repository

    git clone https://github.com/h-tehrani/dorak.git

Switch to the repo folder

    cd dorak

Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

Run the database migrations (**Set the database connection in .env before migrating**)

    php artisan migrate

link storage/app/public with public/storage

    php artisan storage:link

Start the local development server

    php artisan serve

You can now access the server at http://localhost:8000

**TL;DR command list**

    git clone https://github.com/h-tehrani/dorak.git
    cd dorak
    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan storage:link


**Make sure you set the correct database connection information before running the migrations** [Environment variables](#environment-variables)

    php artisan migrate
    php artisan serve


The api can be accessed at [http://localhost:8000/api](http://localhost:8000/api).

----------

# Code overview

## Folders

- `app` - Contains all the Eloquent models
- `app/Http/Controllers/Api` - Contains all the api controllers
- `app/Http/Requests/Api` - Contains all the api form requests
- `app/Repository` - Contains DB layer
- `app/Traits` - Contains Traits
- `config` - Contains all the application configuration files
- `database/factories` - Contains the model factory for all the models
- `database/migrations` - Contains all the database migrations
- `database/seeds` - Contains the database seeder
- `routes` - Contains all the api routes defined in api.php file
- `tests` - Contains all the application tests
- `tests/Feature/Api` - Contains all the api tests
- `Collection` - Contains api collections

## Environment variables

- `.env` - Environment variables can be set in this file

***Note*** : IMMAGA_TOKEN must fill with your basic token.<br><br>
***Note*** : You can quickly set the database information and other variables in this file and have the application fully working.

----------

Request headers

| **Required** 	| **Key**              	| **Value**            	|
|----------	|------------------	|------------------	|
| Yes      	| Content-Type     	| application/json 	|

