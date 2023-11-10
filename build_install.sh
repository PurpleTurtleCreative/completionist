#!/bin/bash

composer install --optimize-autoloader --no-dev

npm ci --no-audit
npm run build
