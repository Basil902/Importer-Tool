<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

# run migrations before phpunit tests
passthru('php bin/console --env=test doctrine:database:create --if-not-exists --quiet');
passthru('php bin/console --env=test doctrine:migrations:migrate --no-interaction --quiet');