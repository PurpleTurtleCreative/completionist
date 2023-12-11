#!/bin/bash

for PATTERN in $(cat ".distignore")
  do rm -rf "./"${PATTERN}
done
