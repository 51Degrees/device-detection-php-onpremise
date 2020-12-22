#!/bin/sh

# Constants
PASSES=20000
PROFILE=MaxPerformance
HOST=localhost:3000
CAL=calibrate.php
PRO=process.php
PERF=./ApacheBench-prefix/src/ApacheBench-build/bin/runPerf.sh


$PERF -n $PASSES -s "php -S $HOST -d FiftyOneDegreesHashEngine.performance_profile=$PROFILE -t ../" -c $CAL -p $PRO -h $HOST
