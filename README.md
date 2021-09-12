# Time tracking app
## Installation and usage
### Project setup
1. clone this repository
2. update .env 'DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name'
3. run composer install
4. run 'php bin/console doctrine:database:create'
5. run 'php bin/console doctrine:migrations:migrate'
6. start integrated server with 'symfony server:start'
7. open 127.0.0.1:8000 in browser or use link from terminal (note: server port can be difirent if desired 8000 is in use)