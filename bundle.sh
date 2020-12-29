#!/bin/bash
VERSION=$(grep -Eio 'Version:\s*[0-9\.]+' completionist.php | grep -Eo '[0-9\.]+')

cd ..
zip -rT9X completionist.zip completionist/ --exclude '*/.git*' '*/.DS_Store' '*.zip' '*.log' '*.map' '*.sh' completionist/README.md completionist/composer.json completionist/composer.lock 'completionist/node_modules/*' completionist/package-lock.json completionist/package.json completionist/terser.json 'completionist/assets/css/scss/*'

echo "Bundled v${VERSION}."