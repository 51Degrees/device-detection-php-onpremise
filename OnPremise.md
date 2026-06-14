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

The Hash engine extension will then be installed into the PHP extensions directory and can then be added to the active php.ini file.

## Configuration

The minimum configuration needed for the extensions is to add it to the active php.ini file, and set the data file. For example:

```
extension=/usr/lib/php/20170718/FiftyOneDegreesHashEngine.so
FiftyOneDegreesHashEngine.data_file=/home/51dDegrees/51Degrees-LiteV4.1.hash
```

is enough to set up the Hash extension with default configuration options.

### More Options

#### Hash

| Option | Type | Description | Default |
| ------ | ---- | ----------- | ------- |
| `required_properties` | `string` | List of properties which are required. Properties not in this list will not be returned. | `""` (all properties) |
| `performance_profile` | `string` | **Deprecated and ignored.** PHP always builds the engine with the `MaxPerformance` (in memory) profile, because it is the only profile that is safe under a process manager such as `Apache MPM` or `php-fpm`. The option is retained for backwards compatibility, but setting it to anything other than `MaxPerformance` has no effect and logs a warning. | `MaxPerformance` (enforced) |
| `difference` | `int` | The difference value to allow when matching (`-1` to disable). | `0` |
| `drift` | `int` | The drift to allow when matching (`-1` to disable). | `0` |
