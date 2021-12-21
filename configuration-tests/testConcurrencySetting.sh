#!/bin/bash

# WARNING: This test will update `php.ini` file and reverse the changes
# once it has finished. It will make a backup of the file before doing
# any further change. If you're not sure, make a backup of the file
# before running the test.
# Also, this test only against `php.ini` files which are under folder
# /etc/php/[version]/cli/

# Current file location
FILE_DIR=`realpath $(dirname $0)`

# Determine the version of PHP
PHP_VERSION=`php-config --version | grep -o '^[0-9]\.[0-9]'`
echo "Testing PHP $PHP_VERSION."

CONFIG_FILE=/etc/php/$PHP_VERSION/cli/php.ini
WRK_CONFIG_DIR=${FILE_DIR}/${PHP_VERSION}
WRK_CONFIG_FILE=${WRK_CONFIG_DIR}/php.ini

DATA_FILE=51Degrees-LiteV4.1.hash
CONCURRENCY_DEFAULT=10
CONCURRENCY_ZERO=0
CONCURRENCY_ONE=1
CONCURRENCY_NEGATIVE=-3

function processRunning {
	local count=`ps -p $1 | grep -c $1`
	if [ "$count" == "0" ]; then
		return 0
	else
		return 1
	fi
}

function stopServer {
	echo "Stop server running at $1"
	kill -n 9 $1

	# Make sure process is not running
	local killed=1
	for i in {0..2}
	do
		processRunning $1
		if [ "$?" == "0" ]; then
			killed=0
			break
		fi
		sleep 1
	done
	if [ "$killed" == "1" ]; then
		echo "FAIL: Failed to stop server"
	fi
	return $killed
}

function clean {
	if test -d "$WRK_CONFIG_DIR"; then
		echo "Remove $WRK_CONFIG_DIR."
		# Delete the working config directory
		rm -rf $WRK_CONFIG_DIR
	fi
}

function runTest {
	local CONCURRENCY=$CONCURRENCY_DEFAULT
	if [ "$#" == "1" ]; then
		CONCURRENCY=$1
	fi

	local EXPECTED=$CONCURRENCY
	if [ $CONCURRENCY -le 0 ]; then
		EXPECTED=$CONCURRENCY_DEFAULT
	fi

	# Make php.ini is available for this test.
	if test ! -f $CONFIG; then
		echo "FAIL: $CONFIG does not exist. This test is meant to run with php.ini under /etc/php/[version]/cli folder."
		exit 1
	fi

	# Assume that php.ini has not been changed
	# Make a copy
	mkdir $WRK_CONFIG_DIR
	if [ $? != 0 ]; then
		echo "FAIL: To create $WRK_CONFIG_DIR."
		exit 1
	fi

	cp $CONFIG_FILE $WRK_CONFIG_FILE
	if [ $? != 0 ]; then
		echo "FAIL: Failed to copy $CONFIG_FILE to $WRK_CONFIG_FILE."
		exit 1
	fi

	# Update the copy
	echo extension=${FILE_DIR}/../on-premise/modules/FiftyOneDegreesHashEngine.so | tee -a $WRK_CONFIG_FILE
	echo FiftyOneDegreesHashEngine.data_file=${FILE_DIR}/../on-premise/device-detection-cxx/device-detection-data/$DATA_FILE | tee -a $WRK_CONFIG_FILE
	# Don't set concurrency if no argument is passed
	if [ "$#" == "1" ]; then
		echo FiftyOneDegreesHashEngine.concurrency=$CONCURRENCY | tee -a $WRK_CONFIG_FILE
	fi

	# Start the target php program
	nohup php -S localhost:3002 -c $WRK_CONFIG_FILE -t ./ ${FILE_DIR}/sample.php 2>&1 1>/dev/null &
	PID=$!
	echo "Sample PHP program is running with PID $PID."

	# Verify that it is running as expected
	started=0
	for i in {0..2}
	do
		status=`curl -s -o /dev/null -I -w "%{http_code}" localhost:3002`
		if [ "$status" == "200" ]; then
			started=1
			break;
		fi
		sleep 1
	done

	if [ "$started" == "0" ]; then
		clean
		echo "FAIL: Failed to start sample PHP program."
		return 1
	fi

	# Verify that Concurrency has been set correctly
	fds=`lsof -p $PID | grep $DATA_FILE | wc -l`

	if [ "$fds" != "$EXPECTED" ]; then
		stopServer $PID
		clean
		echo "FAIL: Concurrency option is not being picked up. Expected $EXPECTED but got $fds."
		return 1
	fi

	# Stop the php program
	stopServer $PID
	if [ $? != 0 ]; then
		clean
		return 1
	fi

	clean
}

function checkResult() {
	if [ "$1" -ne "0" ]; then
		echo "FAIL"
	else
		echo "PASS"
	fi
}

# Store result value
result=0

# Run  test with default concurrency 10
echo "Test 1: Set concurrency to 10 which is the default value"
runTest $CONCURRENCY_DEFAULT
rc=$?
if [ $rc != 0 ]; then
	result=$rc
fi
checkResult $rc
echo ""

# Run test with concurrency = 0
echo "Test 2: Set concurrency to 0"
runTest $CONCURRENCY_ZERO
rc=$?
if [ $rc != 0 ]; then
	result=$rc
fi
checkResult $rc
echo ""

# Run test with concurrency = negative
echo "Test 3: Set concurrency to negative"
runTest $CONCURRENCY_NEGATIVE
rc=$?
if [ $rc != 0 ]; then
	result=$rc
fi
checkResult $rc
echo ""

# Run test with concurrency = 1
echo "Test 4: Set concurrency to 1"
runTest $CONCURRENCY_ONE
rc=$?
if [ $rc != 0 ]; then
	result=$rc
fi
checkResult $rc
echo ""

# Run test with no concurrency
echo "Test 5: Don't set concurrency and use default setting"
runTest
rc=$?
if [ $rc != 0 ]; then
	result=$rc
fi
checkResult $rc
echo ""

# Exit
exit $result
