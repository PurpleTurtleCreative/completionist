#!/bin/bash
VERSION=$(grep -Eio 'Version:\s*[0-9\.]+' completionist.php | grep -Eo '[0-9\.]+')

zip -rT9X ../completionist.zip . --exclude '.git*' '*/.DS_Store' '*.log' '*.map' '*.sh' README.md changelog.md composer.json composer.lock composer.phar 'node_modules/*' package-lock.json package.json terser.json 'assets/css/scss/*'

echo "Bundled v${VERSION}."