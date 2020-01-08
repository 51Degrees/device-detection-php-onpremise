# On-Premise Extensions

## Intrduction

When using on-premise device detection engines in the PHP pipeline, the appropriate extensions will need to be installed. These are contained in this directory.

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

The extensions can then be installed with:

``` bash
phpize
./configure
make
sudo make install
```

To regenerate the SWIG wrapper files, add `SWIG=1` to the `./configure` step.

The Pattern and Hash engine extensions will then be installed into the PHP extensions directory and can then be added to the active php.ini file.

## Configuration

The minimum configuration needed for the extensions is to add it to the active php.ini file, and set the data file. For example:

```
extension=/usr/lib/php/20170718/FiftyOneDegreesHashEngine.so
FiftyOneDegreesHashEngine.data_file=/home/51dDegrees/51Degrees-LiteV3.4.trie

extension=/usr/lib/php/20170718/FiftyOneDegreesPatternEngine.so
FiftyOneDegreesPatternEngine.data_file=/home/51degrees/51Degrees-LiteV3.2.dat
```

is enough to set up both Pattern and Hash extensions with default configuration options.

### More Options

#### Pattern

| Option | Type | Description | Default |
| ------ | ---- | ----------- | ------- |
| `required_properties` | `string` | List of properties which are required. Properties not in this list will not be returned. | `""` (all properties) |
| `performance_profile` | `string` | The performance profile to build the engine with. Available options are `"HighPerformance"`, `"MaxPerformance"`, `"Balanced"`, `"BalancedTemp"`, `"LowMemory"`, `"Default"` | `"Default"` |
| `difference` | `int` | The difference value to allow when matching (`-1` to disable). | `10` |

#### Hash

| Option | Type | Description | Default |
| ------ | ---- | ----------- | ------- |
| `required_properties` | `string` | List of properties which are required. Properties not in this list will not be returned. | `""` (all properties) |
| `performance_profile` | `string` | The performance profile to build the engine with. Available options are `"HighPerformance"`, `"MaxPerformance"`, `"Balanced"`, `"BalancedTemp"`, `"LowMemory"`, `"Default"` | `"Default"` |
| `difference` | `int` | The difference value to allow when matching (`-1` to disable). | `0` |
| `drift` | `int` | The drift to allow when matching (`-1` to disable). | `0` |
