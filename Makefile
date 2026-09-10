.PHONY: test test-unit test-integration test-filter test-db compile-assets server messenger-worker-async

test:
	php bin/phpunit

test-unit:
	vendor/bin/phpunit --testsuite unit

test-integration:
	vendor/bin/phpunit --testsuite integration


test-db:
	php bin/console --env=test doctrine:database:create --if-not-exists
	php bin/console --env=test doctrine:migrations:migrate --no-interaction

migrate:
	php bin/console doctrine:migrations:migrate --no-interaction

diff:
	php bin/console doctrine:migrations:diff

db:
	php bin/console doctrine:migrations:create --if-not-exists

load-fixtures:
	php bin/console doctrine:fixtures:load --no-interaction

load-fixtures-test:
	php bin/console doctrine:fixtures:load --group=testing --no-interaction

compile-assets:
	php bin/console asset-map:compile

server:
	PHP_CLI_SERVER_WORKERS=4 symfony server:start

messenger-worker-async:
	symfony console messenger:consume async -vv

clear-cache:
	symfony console cache:clear

clear-cache-test:
	symfony console cache:clear --env=test