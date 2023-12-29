.POSIX: # in order to have reliable POSIX behavior 
SHELL: /bin/sh

# Variables (for value reuse)
UID := $(shell id -u)
GID := $(shell id -g)


# Macros for instruction reuse
define dc
	docker-compose
endef

define dc-uid
	UID=$(UID) GID=$(GID) $(dc)
endef


# Build docker images
build:
ifndef service
	$(dc-uid) build
else
	$(dc-uid) build $(service)
endif

# Create new containers of docker-compose services
up:
ifdef service
	$(dc-uid) up -d $(service)
else ifeq ($(mode), server)
	$(dc-uid) up -d php nginx db adminer
else
	$(dc-uid) up -d
endif

up-port:
ifeq ($(service), adminer)
	ADMINER_HOST_PORT=$(port) $(dc-uid) up -d $(service)
else ifeq ($(service), maildev)
	MAILDEV_WEB_PORT=$(port) $(dc-uid) up -d $(service)
else ifeq ($(service), matomo)
	MATOMO_PORT=$(port) $(dc-uid) up -d $(service)
else ifeq ($(service), nginx)
	SERVER_HOST_PORT=$(port) $(dc-uid) up -d $(service)
endif

ps:
	$(dc) ps

# Stop docker-compose instances
stop:
ifndef service
	$(dc) stop
else
	$(dc) stop $(service)
endif

# Removing docker-compose running services
down:
	$(dc-uid) down

# Restart docker-compose services
restart:
ifndef service
	$(dc) restart
else
	$(dc) restart $(service)
endif

# Start docker-compose services
start:
ifndef service
	$(dc) start
else
	$(dc) start $(service)
endif


# Read logs for a docker-compose service
logs:
	$(dc) logs -f $(service)

# Starts a /bin/sh shell in a docker-compose service
sh:
	$(dc) exec $(service) sh

# Starts a /bin/bash shell in a docker-compose service
bash:
	$(dc) exec $(service) bash

composer:
	$(dc) exec php composer $(arg)

sf-console:
	$(dc) exec php bin/console $(arg)

yarn-prod-build:
	$(dc-uid) run -u $(UID):$(GID) --rm node yarn build

yarn:
	$(dc-uid) run -u $(UID):$(GID) --rm node yarn $(arg)

# Starts the CLI of redis-cli
redis-cli:
	$(dc) exec redis redis-cli

# Clear all keys in redis service
redis-flush:
	$(dc) exec redis redis-cli flushall

# Starts a mysql CLI from docker-compose's db service
db-cli:
	$(dc) exec db mysql -u root -proot coupcritique

# Import an SQL file into docker-compose's db service
sql-import:
	$(dc) exec -T db mysql -u root -proot coupcritique < $(path)

sql-dump:
	$(dc) exec -T db mysqldump -u root -proot coupcritique > coupcritique_$(shell date +"%Y-%m-%d").sql
