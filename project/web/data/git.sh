#!/bin/bash
path=$1
currentTimeStamp=$2

cd $path
git pull
echo $currentTimeStamp > README.md
git add .
git commit -m "$currentTimeStamp"
git push