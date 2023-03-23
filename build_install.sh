#!/bin/bash

composer install --optimize-autoloader

npm ci --no-audit
npm run build
