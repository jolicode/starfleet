#!/usr/bin/env bash

bin/console doctrine:database:create --if-not-exists
bin/console doctrine:migrations:migrate --no-interaction

npm install
npm run build
