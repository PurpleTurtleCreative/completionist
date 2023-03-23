#!/bin/bash

for PATTERN in $(cat "exclude.lst")
  do rm -rf "../"${PATTERN}
done
