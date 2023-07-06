# SSL

`local-ssl-proxy` is used to create the SSL server.

https://www.npmjs.com/package/local-ssl-proxy

Ensure node and npm have been installed. The following steps have been tested on
Ubuntu 22 with PHP 8.1.

Run the following commands to install and setup.

```bash
npm install -g local-ssl-proxy
sudo apt install mkcert
mkcert -install
mkcert localhost
```

The result will be a trusted authority installed on the development machine and
the two certificate files localhost-key.pem and localhost.pem.

Then run the proxy server with the SSL certificates.

```bash
local-ssl-proxy --config config.json
```

Ports 3001 and 3002 will now map to port 300 and support HTTPS connections. This
enable secure origin verification.

# Example

Now run the example as normal using the standard example.

```bash
 php -c php.ini -S localhost:3000 gettingStartedWeb.php
```

# What's happening?

The `static/page.php` has been modified to request the JavaScript and JSON 
resources from the host `localhost:3002`. When the page is requested from 
`localhost:3001` then a cross origin resource (CORS) request will be taking place 
because the the port numbers are different. The provided example does not 
consider CORS and has not been configured for this scenario. A console message 
will be generated in the web browser.

```log
Access to XMLHttpRequest at 'https://localhost:3002/json' from origin 
'https://localhost:3001' has been blocked by CORS policy: No 
'Access-Control-Allow-Origin' header is present on the requested resource.
```

The `gettingStartedWeb.php` example has been changed to address this warning via
the addition of the following code.

```PHP
header('Access-Control-Allow-Origin: *');
```

# php.ini

The php.ini in this folder should be used with the example on a standard Ubuntu
22 host. The following lines are added to support the on premise PHP extension 
for 51Degrees.

```conf
extension=/usr/lib/php/20210902/FiftyOneDegreesHashEngine.so
FiftyOneDegreesHashEngine.required_properties=DeviceId,JavascriptHardwareProfile,HardwareVendor,HardwareModel,HardwareName,IsMobile,JavascriptGetHighEntropyValues,Promise,Fetch,DeviceType,PlatformVendor,PlatformName,PlatformVersion,BrowserVendor,BrowserName,BrowserVersion,ScreenPixelsWidth,ScreenPixelsHeight,ScreenPixelsWidthJavascript,ScreenPixelsHeightJavascript
```

The required properties is important to reduce the amount of unneeded data that
could be sent in the JavaScript and JSON responses. Adding the properties 
`JavascriptHardwareProfile` and `JavascriptGetHighEntropyValues` ensures that
the JavaScript included in the example page via 
`<script async src="https://localhost:3002/js" type="text/javascript"></script>`
includes the logic to resolve User Agent Client Hints and also Apple models and
groups of models.