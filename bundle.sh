#!/bin/bash

# Determine package values.

PLUGIN_SLUG=$( basename `pwd` )

VERSION=$(grep -Eio 'Version:\s*[0-9]+\.[0-9]+\.[0-9]+(\-rc\.[0-9]+)?' "${PLUGIN_SLUG}.php" | grep -Eio '[0-9]+\.[0-9]+\.[0-9]+(\-rc\.[0-9]+)?')

# Create the plugin package.

pushd ..

PLUGIN_ZIP_FILE=$( pwd )/"${PLUGIN_SLUG}-${VERSION}.zip"

zip -rT9X "${PLUGIN_ZIP_FILE}" "${PLUGIN_SLUG}" --exclude @"${PLUGIN_SLUG}"/.distignore

popd

# Maybe export variables for GitHub Action.

if [ -n "$GITHUB_ENV" ]; then
  echo "PTC_PLUGIN_ZIP_FILE_BASENAME=$( basename -s '.zip' "$PLUGIN_ZIP_FILE" )" >> "$GITHUB_ENV"
  echo "PTC_PLUGIN_ZIP_FILE=${PLUGIN_ZIP_FILE}" >> "$GITHUB_ENV"
  echo "Exported GitHub ENV variables."
else
  echo "Did not set \$GITHUB_ENV variables."
fi

# Final notes.

echo
echo "!! Finished bundling plugin package: ${PLUGIN_ZIP_FILE}"
echo
