#!/bin/bash
VERSION=$(grep -Eio 'Version:\s*[0-9\.]+' completionist.php | grep -Eo '[0-9\.]+')

# zip -rT9X ../completionist.zip ./ --exclude '*.git*' '*.DS_Store' '*.zip' '*.log' '*.map' '*.sh' README.md composer.json composer.lock composer.phar '*node_modules/*' package-lock.json package.json terser.json 'assets/css/scss/*'

cd ..
touch .tmp
zip -rT9X completionist.zip .tmp completionist/ --exclude '*.git*' '*.DS_Store' '*.zip' '*.log' '*.map' '*.sh' README.md composer.json composer.lock composer.phar '*node_modules/*' '*vendor/*' package-lock.json package.json terser.json 'assets/css/scss/*'
zip -d completionist.zip .tmp
rm -f .tmp

echo "Bundled v${VERSION}."