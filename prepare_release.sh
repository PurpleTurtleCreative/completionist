#!/bin/bash

# Get the last version tag
readonly LAST_VERSION_TAG=$(git describe --abbrev=0 --tags)

# Prompt user for new version
read -p "Enter the new version number (most recent tag: ${LAST_VERSION_TAG#"v"}): " NEW_VERSION
readonly NEW_VERSION

# Validate input: Check if the input is a valid version number
if ! [[ $NEW_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Error: Invalid version number. Please try again with proper semver formatting."
	exit 1
fi

#######################################

# Replace [unreleased] placeholders throughout src files
grep -FRl --exclude='*/node_modules/*' --exclude='*/vendor/*' '[unreleased]' completionist.php src assets | xargs sed -i -e "s/\[unreleased\]/$NEW_VERSION/g"

# Replace header value in main plugin file.
sed -Ei "s/(Version:\s+)[^\s]+/\1$NEW_VERSION/" completionist.php

# Replace header value in readme.txt for WordPress.org plugin page
sed -i "s/Stable tag: .*/Stable tag: $NEW_VERSION/" readme.txt

# Replace release heading in changelog.md with specified new version and current date
readonly TODAY=$(date +'%Y-%m-%d')
sed -i "s/### \[unreleased\]/### $NEW_VERSION - $TODAY/" changelog.md

#######################################

# Review and confirm changes
echo
echo "Release checklist changes applied."
echo "Please review the changes and confirm this release:"
echo
echo "git status"
echo "git diff"
echo
echo "git commit -m \"bump v$NEW_VERSION\""
echo "git tag -a \"v$NEW_VERSION\" -m \"see changelog.md\""
echo "git push --tags"
echo
