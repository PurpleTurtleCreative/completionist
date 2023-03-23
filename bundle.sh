#!/bin/bash

PLUGIN_SLUG=$( basename `pwd` )

VERSION=$(grep -Eio 'Version:\s*[0-9\.]+' "${PLUGIN_SLUG}.php" | grep -Eo '[0-9\.]+')

cd ..
zip -rT9X "${PLUGIN_SLUG}-${VERSION}.zip" "${PLUGIN_SLUG}" --exclude @"${PLUGIN_SLUG}"/exclude.lst
