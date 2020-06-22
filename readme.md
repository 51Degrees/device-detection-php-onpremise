![51Degrees](https://51degrees.com/DesktopModules/FiftyOne/Distributor/Logo.ashx?utm_source=github&utm_medium=repository&utm_content=readme_main&utm_campaign=php-open-source "Data rewards the curious") **PHP Pipeline API**

[Developer Documentation](https://docs.51degrees.com?utm_source=github&utm_medium=repository&utm_content=documentation&utm_campaign=php-open-source "developer documentation")

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

These can be installed through apt using:

``` bash
sudo apt-get install g++ php php-dev make
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

If the SWIG wrapper files need to be regenerated due to new code in the 'device-detection-cxx' submodule, add `SWIG=1` to the `./configure` step.

The Hash engine extension will then be installed into the PHP extensions directory and can then be added to the active php.ini file.

## Data File

In order to perform device detection, you will need to use a 51Degrees data file. This repository includes a free, 'lite' file in the 'device-detection-data' sub-module that has a significantly reduced set of properties. To obtain a file with a more complete set of device properties see the (51Degrees website)[https://51degrees.com/pricing]. 
If you want to use the lite file, you will need to install (GitLFS)[https://git-lfs.github.com/]:

``` bash
sudo apt-get install git-lfs
git lfs install
```

Then, navigate to `on-premise/device-detection-cxx/device-detection-data` and execute:

``` bash
git lfs pull
```

## Configuration

The minimum configuration needed for the extensions is to add it to the active php.ini file, and set the data file. For example:

```
extension=/usr/lib/php/20170718/FiftyOneDegreesHashEngine.so
FiftyOneDegreesHashEngine.data_file=/home/51Degrees/51Degrees-LiteV4.1.hash
```

is enough to set up the Hash extension with default configuration options.

### More Options

#### Hash

| Option | Type | Description | Default |
| ------ | ---- | ----------- | ------- |
| `required_properties` | `string` | List of properties which are required. Properties not in this list will not be returned. | `""` (all properties) |
| `performance_profile` | `string` | The performance profile to build the engine with. Available options are `"HighPerformance"`, `"MaxPerformance"`, `"Balanced"`, `"BalancedTemp"`, `"LowMemory"`, `"Default"` | `"Default"` |
| `difference` | `int` | The difference value to allow when matching (`-1` to disable). | `0` |
| `drift` | `int` | The drift to allow when matching (`-1` to disable). | `0` |

## Examples

To run the examples, you will need PHP and composer installed.
Once these are available, install the dependencies required by the examples. 
Navigate to the repository root and execute:

```
composer install
```

This will create the vendor directory containing autoload.php. 
Now navigate to the examples directory and start a PHP server with the relevant file. For example:

```
PHP -S localhost:3000 gettingstarted.php
```

This will start a local web server listening on port 3000. 
Open your web browser and browse to http://localhost:3000/ to see the example in action.


## Tests

This repo has tests for the examples. To run the tests, make sure PHPUnit is installed then, in the root of this repo, call:

```
phpunit --log-junit test-results.xml
```
