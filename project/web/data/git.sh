#!/bin/bash
path=$1
current=`date "+%Y-%m-%d %H:%M:%S"`
timeStamp=`date -d "$current" +%s`
currentTimeStamp=$((timeStamp*1000+`date "+%N"`/1000000)) 

cd $path
echo $currentTimeStamp > README.md
git add .
git commit -m $currentTimeStamp
git push