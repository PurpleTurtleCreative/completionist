#!/bin/bash

# Currently, only dev dependencies are included,
# so installation is unnecessary.
# composer install --optimize-autoloader --no-dev

# Install our own fork of Asana's PHP SDK because
# they haven't pushed updates in a very long time
# and we include our own repairs for PHP 8 compatibility.
pushd php-asana
composer install --optimize-autoloader --no-dev
popd

npm ci --no-audit
npm run build
