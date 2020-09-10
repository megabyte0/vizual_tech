## Abstract
Basically it's a fresh bitnami/laravel docker installation with beginning of the test assignment project.
## Usage  
To use: `docker-compose up`  
Update/load json into db: `docker-compose exec myapp php artisan import:json ./storage/prods.json`  
`php artisan import:json ./storage/prods.json` also can be used in cron. 
