Table of contents
=================
* [Installation](#installation)
  * [Local setup](#local-setup)
    * [Requirements](#local-requirements)
    * [Steps](#local-steps)
    * [Utilities](#local-utilities)
  * [Docker setup](#docker-setup)
    * [Requirements](#docker-requirements)
    * [Steps](#docker-steps)
    * [Utilities](#docker-utilities)
      * [List](#list)
      * [Switch port](#switch-port)
* [Post-installation](#post-installation)
    * [Quality tools](#quality-tools)
    * [Feed the database](#feed-the-database)

# Installation

Clone the repository
```
git clone git@github.com:GeoDaz/coupcritique
```

## Local setup

<h3 id="local-requirements">Requirements</h3>

- PHP 7.4
- PHPCSFixer executable (must be installed if you want to use it)
- MariaDB 10.2
- Composer
- NodeJS 16 with Yarn
- Redis (for production mode)
- OpenSSL

<h3 id="local-steps">Steps</h3>

#### 1. Create .env.local from .env
```bash
cp .env .env.local
```

Fill the `DATABASE_URL` with the correct parameters of your MariaDB server instance.

#### 2. Install composer dependencies

```bash
composer install
```

#### 3. Create the database if it doesn't exist
```bash
php bin/console doctrine:database:create
```

#### 4. Update the database with entities schema
```bash
php bin/console doctrine:schema:update --force
```

#### 5. Generate the JWT token creation keypair

```bash
openssl genrsa -out config/jwt/private.pem -aes256 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

And then fill your .env.local file :
```bash
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE="your secret phrase"
```

#### 8. Start Symfony App

Through the built-in PHP development server : 
```bash
php -S 127.0.0.1:<desired port> -t public
```

Through Symfony CLI : 

```bash
symfony server:start 
```

In detached mode : 

```bash
symfony server:start -d 
```

<h3 id="local-utilities">Utilities</h3>

#### Check and validate database schema

```bash
php bin/console doctrine:mapping:info
php bin/console doctrine:schema:validate
```

#### Create admin user
```bash
php bin/console doctrine:fixtures:load 
```

## Docker setup

<h3 id="docker-requirements">Requirements</h3>

- Docker (with root access)
- docker-compose
- Makefile (optional)

### Steps

The project has also a Docker development environment for more convenience.
All steps commands are shown with the docker-compose syntax with its *make counterpart*.

You can choose the *make* one, if you've installed it on your machine.

#### 1. Create .env.local from .env

```bash
cp .env .env.local
```

No need to fill the `DATABASE_URL`, it will be determined through docker-compose.


#### 2. Build docker images

docker-compose command : 
```bash
UID=$(id -u) GID=$(id -g) docker-compose build
```

make command : 
```bash
make build
```

#### 3. Create containers

docker-compose command :
```bash
UID=$(id -u) GID=$(id -g) docker-compose up -d
```

make command :
```bash
make up
```

#### 4. Install composer dependencies

docker-compose command :
```bash
docker-compose exec php composer install
```

make command :
```bash
make composer arg=install
```

#### 5. Create database if it doesn't exist

docker-compose command : 
```bash
docker-compose exec php bin/console doctrine:database:create
```

make command :
```bash
make sf-console arg=doctrine:database:create
```

#### 6. Update the database with entities schema

docker-compose command :
```bash
docker-compose exec php bin/console doctrine:schema:update --force
```

make command : 
```bash
make sf-console arg="doctrine:schema:update --force"
```

<h3 id="docker-utilities">Utilities</h3>

#### List

<table>
  <tr>
    <th>Utility</th>
    <th>docker-compose command</th>
    <th>make command</th>
  </tr>
  <tr>
    <td>Stop all services</td>
    <td>docker-compose stop</td>
    <td>make stop</td>
  </tr>
  <tr>
    <td>Stop one service</td>
    <td>docker-compose stop &lt;service&gt;</td>
    <td>make stop service=&lt;service&gt;</td>
  </tr>
  <tr>
    <td>Stops and remove all services</td>
    <td>docker-compose down</td>
    <td>make down</td>
  </tr>
  <tr>
    <td>Starts a shell interpreter into a service</td>
    <td>docker-compose exec &lt;service&gt; sh</td>
    <td>make sh service=&lt;service&gt;</td>
  </tr>
  <tr>
    <td>Starts a bash interpreter into a service</td>
    <td>docker-compose exec &lt;service&gt; sh</td>
    <td>make bash service=&lt;service&gt;</td>
  </tr>
  <tr>
    <td>Run composer with provided arguments (must be surrounded by double quotes for the make command if it has spaces)</td>
    <td>docker-compose exec php composer &lt;args&gt;</td>
    <td>make composer args=&lt;args&gt;</td>
  </tr>
  <tr>
    <td>Start a CLI into coupcritique MariaDB server</td>
    <td>docker-compose exec db mysql -u root -proot coupcritique</td>
    <td>make db-cli</td>
  </tr>
  <tr>
    <td>Starts a redis CLI</td> 
    <td>docker-compose exec redis redis-cli</td>
    <td>make redis-cli</td> 
  </tr>
  <tr>
    <td>Flush the redis cache</td>
    <td>docker-compose exec redis redis-cli flushall</td>
    <td>make redis-flush</td>
  </tr>
  <tr>
    <td>Import a SQL file into the MariaDB service</td>
    <td>docker-compose exec -T db mysql -u root -proot coucpritique < &lt;path&gt;</td>
    <td>make sql-import path="&lt;path&gt;"</td>
  </tr>
  <tr>
    <td>Produce a dump of the MariaDB service's coupcritique database with today's timestamp</td>
    <td>docker-compose exec -T db mysqldump -u root -proot coucpritique > coupcritique_$(date +"%Y-%m-%d").sql</td>
    <td>make sql-dump</td>
  </tr>
</table>

#### Switch port

By default docker-compose's services are using the following ports : 

- MariaDB : 8336
- Nginx : 7800
- adminer : 8888
- maildev : 8700
- matomo : 8889

You can easily switch their ports thanks to make : 

```bash
make up-port service=<your service> port=<new port>
```

# Post-installation

## Quality tools

GrumPHP can be used for this project.

If you want to use it, create `grumphp.yml` from `grumphp.yml.dist`.
Then, you can customize the rules at your convenience :

- For PHPCSFixer : create `.php-cs-fixer.php` from `.php-cs-fixer.dist.php` and uncomment the rule in your GrumPHP's config.

- For PHPCodeSniffer : create `phpcs.xml` from `phpcs.xml.dist`.

- For PHPStan : create `phpstan.neon` from `phpstan.neon.dist` and replace `"phpstan.neon.dist"` in your GrumPHP's config.
## Feed the database

After the installation, you must feed your database, especially for Showdown's data.
To do so, you must download **a dump** in dev.coupcritique@gmail.com's Google Drive (or ask an administrator to give you one) and import it.

### For a local installation

```bash
mysql -u <user> -p<password> coupcritique < <path of the downloaded dump>
```

### For a docker installation

docker-compose command :
```bash
docker-compose exec -T db mysql -u <user> -p<password> coupcritique < <path of the downloaded dump>
```

make command : 
```bash
make sql-import path="<path of the downloaded dump>" 
```
