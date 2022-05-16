# 51Degrees PHP Device Detection On-Premise

![51Degrees](https://51degrees.com/DesktopModules/FiftyOne/Distributor/Logo.ashx?utm_source=github&utm_medium=repository&utm_content=readme_main&utm_campaign=php-open-source "Data rewards the curious") **PHP Pipeline API**

[Developer Documentation](https://51degrees.com/device-detection-php/index.html?utm_source=github&utm_medium=repository&utm_content=documentation&utm_campaign=php-open-source "developer documentation")

## Introduction
This project contains the on-premise version of 51Degrees Device Detection for the Pipeline API.

When using on-premise device detection engines in the PHP pipeline, the appropriate extensions will need to be installed.

## Installing

### Linux

To install on Linux you will need the following dependencies installed:

- g++
- php
- php-dev
- make
- libatomic1

These can be installed through apt using:

``` bash
sudo apt-get install g++ php php-dev make libatomic1
```

We need to ensure that the necessary sub-modules, containing the native C code, have been cloned.
If cloning for the first time, use:

``` bash
git clone --recurse-submodules -j8 https://github.com/51Degrees/device-detection-php-onpremise.git
```

If you have already cloned the repository and want to fetch the sub modules, use:

``` bash
git submodule update --init --recursive
```

Now, we can create the extension. Navigate to the `on-premise` directory and install it with:

``` bash
phpize
./configure
make
sudo make install
```

The Hash engine extension will then be installed into the PHP extensions directory and can 
then be added to the active php.ini file.

#### Regenerating swig wrapper files.

**Note** - this step should not normally need to be performed outside 51Degrees. 
We include the instructions here for the rare case where it is necessary.

If the SWIG wrapper files need to be regenerated due to new code in the 'device-detection-cxx'
 submodule, add `SWIG=1` to the `./configure` step. Note that for backwards compatibility with
 PHP 5, SWIG 3.0.12 is used for the pregenerated files in this repository. Newer versions of
 SWIG can be used, provided the extension is being built only for PHP 7.

Currently, The generation of SWIG wrapper files is using a specific revision of SWIG and is done
using the following steps:

Checkout and install SWIG:

```
git clone https://github.com/swig/swig.git
cd swig
git checkout fd96627b2fc65353c03b160efd60fdce864d386c
./autogen.sh
./configure
make
sudo make install
```

Generate wrapper files:

```
$:~/device-detection-php-onpremise
cd on-premise
phpize
swig -v -c++ -php7 -module FiftyOneDegreesHashEngine -outdir src/php8 -o src/php8/hash_wrap.cxx hash-php.i
```

### Windows 

Although building extensions on Windows is possible, we recommend using the 
[Windows Subsystem for Linux](https://docs.microsoft.com/en-us/windows/wsl/install-win10) 
and following the instructions for Linux above.

## Data File

In order to perform device detection, you will need to use a 51Degrees data file. This repository 
includes a free, 'lite' file in the 'device-detection-data' sub-module that has a significantly 
reduced set of properties. To obtain a file with a more complete set of device properties see the 
[51Degrees website](https://51degrees.com/pricing). 
If you want to use the lite file, you will need to install [GitLFS](https://git-lfs.github.com/):

``` bash
sudo apt-get install git-lfs
git lfs install
```

Then, navigate to `on-premise/device-detection-cxx/device-detection-data` and execute:

``` bash
git lfs pull
```

## Configuration

The minimum configuration needed for the extensions is to add it to the active php.ini file, 
and set the data file. For example:

<pre>
<code>extension=/usr/lib/php/<span style="background-color: #FFFF00">20170718</span>/FiftyOneDegreesHashEngine.so
FiftyOneDegreesHashEngine.data_file=/<span style="background-color: #FFFF00">path to your file</span>/51Degrees-LiteV4.1.hash
</code>
</pre>
is enough to set up the Hash extension with default configuration options.

NOTE: Make sure to check the highlighted parts. The first is dependent on the
PHP version. This location will be printed when installing. The second is the
path to where you have stored your 51Degrees data file.

### More Options

#### Hash

| Option | Type | Description | Default |
| ------ | ---- | ----------- | ------- |
| `required_properties` | `string` | List of properties which are required. Properties not in this list will not be returned. | `""` (all properties) |
| `performance_profile` | `string` | The performance profile to build the engine with. Available options are `"HighPerformance"`, `"MaxPerformance"`, `"Balanced"`, `"BalancedTemp"`, `"LowMemory"`, `"Default"`. (`NOTE`: Under environment where `PHP` processes are managed by a process manager such as with `Apache MPM` or `php-fpm`, Only `MaxPerformance` should be used. More details can be seen in [Running Under Process Manager](#running-under-process-manager). | `"Default"` |
| `difference` | `int` | The difference value to allow when matching (`-1` to disable). | `0` |
| `drift` | `int` | The drift to allow when matching (`-1` to disable). | `0` |
| `use_predictive_graph` | `string` | True if the predictive optimized graph should be used for processing. | `true` |
| `use_performance_graph` | `string` | True if the performance optimized graph should be used for processing. | `false` |
| `concurrency` | `int` | Maximum number of potential concurrent threads that the engine will be used with. We recommend that this should not be set higher than the number of CPUs available on your machine and this should never be set to be less than or equal to 0. `NOTE`: If a value which is less than or equal to 0 is specified, default value will be used. | `10` |
| `update_matched_useragent` | `string` | True if the detection should record the matched characters from the target User-Agent. | `true` |
| `max_matched_useragent_length` | `int` | Number of characters to consider in the matched User-Agent. Ignored if `update_matched_useragent` is false. | `500` |

### Running Under Process Manager

When `PHP` program is run under a process manager such as `Apache MPM`, `php-fpm` or any other process manager, `MaxPerformance` profile must be specified for `performance_profile` option. `HighPerformance` can also be used but the performance cannot be compared to `MaxPerformance` profile.

The reason is that, in other profiles such as `Balanced`, `BalancedTemp`, `LowMemory` or `Default`, only a part of the data file is loaded into memory so from time to time, calls to read data from disk are required. This call use file handles from a file handle pool which was designed to optimise the performance. However, this pool was created when Device Detection engine module is loaded in the main process, so when process manager spawn child processes in response to incoming requests by forking the main process, this pool is copied to the child processes, causing the file handles to be shared. When multiple processes call to load data from file using shared file handles, problems could occur such as file position being changed unwantedly by other child processes. Thus, we recommend the `MaxPerformance` to be used as no call to data file is required.

While this is an issue in multi-processing environment, it is not a problem in multi-threading environment as the file handles are not shared and are managed by the file handle pool.

#### Apache MPM

When running under Apache MPM, only `prefork` mode is supported, since `dl()` call is required to load Device Detection engine module and it is not supported under multi-threading environment which `worker` and `event` mode does.

#### Refresh Internal Data

To reload the data file, *refreshData()* needs to be called as described in this [example](http://51degrees.com/documentation/4.3/_examples__device_detection__manual_data_reload__on_premise_hash.html) . Thus, unless a reload is performed in the main process and all child processes, the data will not be updated. There is not a proven solution to do so yet, so we recommend a full server restart to be performed instead.

## Examples

Before running the examples, the configuration for the native module MUST be
added to the php.ini file. See [Configuration](#configuration).

To run the examples, you will need PHP and composer installed.
Once these are available, install the dependencies required by the examples. 
Navigate to the repository root and execute:

```
composer install
```

This will create the vendor directory containing autoload.php. 
Now navigate to the examples directory and start a PHP server with the relevant file. For example:

```
php -S localhost:3000 gettingStartedWeb.php
```

This will start a local web server listening on port 3000. 
Open your web browser and browse to http://localhost:3000/ to see the example in action.

The table below describes the examples that are available.

| Example                                | Description |
|----------------------------------------|-------------|
| gettingStartedConsole                  | How to use the 51Degrees on-premise device detection API to determine details about a device based on its User-Agent and User-Agent Client Hints HTTP header values. |
| gettingStartedWeb                      | How to use the 51Degrees Cloud service to determine details about a device as part of a simple ASP.NET website. |
| metadataConsole                        | How to access the meta-data that relates to the device detection algorithm. |
| manualDataUpdate                       | How to update the device detection data file when a new one is available. |
| matchMetrics                           | Demonstrate the metrics that supply information such as confidence in the result. |
| failureToMatch                         | Demonstrates the functionality available when device detection is unable to identify the details of the device. |
| userAgentClientHints-Web               | Legacy example. Retained for the associated automated tests. See GettingStarted-Web instead. |


## Tests

### PHPUnit

This repo has tests for the examples. To run the tests, make sure PHPUnit is installed then, in the root of this repo, call:

```
phpunit --log-junit test-results.xml
```

### Performance

Performance tests for the engine can be found in the `performance-tests` directory. 
To build this, the dependencies listed on [ApacheBench](https://github.com/51degrees/apachebench)
must be installed.

To build the tests, enter the `performance-tests` directory and run the following.

```
mkdir build
cd build
cmake ..
cmake --build .
```

Once the build is completed, ensure that the `php.ini` file is correctly configured, and run 
the tests from the build directory with:

```
./runPerf.sh
```

This will give two output files: `calibrate.out` and `process.out`. These detail the performance 
for a calibration page where there is no pipeline processing, and for a page which processes the 
request through a pipeline containing the device detection on-premise engine, and fetches 
properties.

## Development

When making changes to this repository, it may be necessary to link to a local development 
version of pipeline dependencies. For information on this, 
see [Composer local path](https://getcomposer.org/doc/05-repositories.md#path).

For exmaple, if a development version of `51degrees/fiftyone.pipeline.core` 
was stored locally, the location would be added with:

```
"repositories": [
	{
		"type": "path",
		"url": "../../path/to/packages/pipeline-php-core"
	}
]
```

then the dependency changed to:

```
"51degrees/fiftyone.pipeline.core": "*"
```
