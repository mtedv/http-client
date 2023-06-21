HTTP Client
===========

A simple, object oriented abstraction layer on top of cURL with a straightforward API, making HTTP request easy.

Usage
-----
There are two ways to use the library: Either via the client, which provides some convenience methods, or by directly
creating an `HttpRequest` instance. The former does exactly that, but provides convenient (and typed!) shorthand methods
to create request objects.

```php
# Using HttpClient
$responseBody = CodeWorx\Http\HttpClient::get('https://google.com')->run()->getBody();

# Using HttpRequest
$request = new \CodeWorx\Http\HttpRequest('GET', 'https://google.com');
$responseBody = $request->run()->getBody();
```

Requests
--------
The `HttpRequest` class provides lots of methods to pass request parameters. All of them return the request instance, so
they can simply be chained:

```php
# Using HttpClient
HttpClient::get('https://google.com')
    ->withParam('name', 'value') // will be appended to the request URL
    ->withHeader('x-test-header', 'first') // some well-known headers are available as constants on HttpRequest
    ->withHeader('x-test-header', 'second') // merges multiple values with the same name
    ->withBody(['field' => 'value']) // will be encoded automatically depending on content type header
    ->withAuthorization(HttpRequest::AUTHORIZATION_BASIC, 'user', 'password') // creates the Authorization header
    ->withCurlOption(CURLOPT_FOLLOWLOCATION) // accepts any CURLOPT_ constant. Empty value will be interpreted as true.
    ->withSSLClientCertificate('/var/ssl/cert.pem') // holds the client ssl certificate
    ->withSSLClientCertificatePassword('secret123') // holds the client ssl certificate's password
    ->asJson() // adds a Content-Type header, resulting in the body being JSON encoded
    ->run();
```

All the options configured via these options are interpreted within the `run()` call, just before `curl_exec`. This 
means you can freely add or remove stuff before the request is actually submitted.
The following list of methods is available:

### Query parameters
#### `withParam(string $name, [mixed $value = null]): HttpRequest`
Appends a query parameter to the URL. 

#### `withoutParam(string $name): HttpRequest`
Removes a query parameter from the URL, if set.

#### `withParams(array $params): HttpRequest`
Adds multiple query parameters to the URL.

#### `getParam(string $name): mixed`
Retrieves a query parameter that has been previously set.  
If the parameter has not been previously set, it will return `null`.

#### `getParams(): array`
Retrieves all query parameters that have been previously set.

### Request headers
#### `withHeader(string $name, [mixed $value = null], [bool $replace = false]): HttpRequest`
Adds a header to the request. Unless the `replace` flag is set to true, adding a header with the same name twice will
simply be appended and both headers will be sent. When replacing a header, any prior header of that name (no matter how
many) will be replaced with the new one.

#### `withoutHeader(string $name): HttpRequest`
Removes a header (and all of its values) from the request.

#### `withHeaders(array $headers, [bool $replaceAll = true]): HttpRequest`
Adds multiple headers to the request. In contrary to the 
[`withHeaders`](#withheaderstring-name-mixed-value--null-bool-replace--false-httprequest) method, the `replaceAll` 
parameter is set to `true` by default - so setting multiple headers at once will replace any existing variants.

#### `getHeader(string $name, [bool $first = true]): string|null`
Retrieves a header that has been previously set. Unless setting `$first` to `false`, the method will return only the 
first value, otherwise it will return an array of all values for the header.  
If the header has not been previously set, it will return `null`.

#### `getHeaders(): array`
Retrieves all headers that have been previously set.

### cURL options
#### `withCurlOption(int $option, [mixed $value = true]): HttpRequest`
Adds a cURL option to the request. The value defaults to true, so it's usually enough to simply pass the option to set.
The method accepts any `CURLOPT_` constant as defined by PHP.  
As with the other setters, the options will be set when actually executing the request, therefore you can update or 
remove cURL options later on.

#### `withoutCurlOption(int $option): HttpRequest`
Removes a cURL option from the request.

#### `getCurlOption(int $option): mixed`
Retrieves the value of a cURL option that has been previously set.

#### `getCurlOptions(): array`
Retrieves all cURL options that have been previously set.

### Request body
#### `withBody(mixed $body): HttpRequest`
Sets the request body. Any non-string value will be encoded according to the [content type](#content-type).

### Request URL
#### `withUrl(string $url): HttpRequest`
Sets the request URL.

#### `getUrl(): string`
Retrieves the request URL.

### Authentication
#### `withAuthorization(string $type, string $usernameOrToken, [string $password = '']): HttpRequest`
Adds an `Authorization` header to the request. All of the three officially available authentication types are available
as class constants:
 - `HttpRequest::AUTHORIZATION_BASIC`:  
   Basic authentication. Requires username and password, which will automatically be base64-encoded.
 - `HttpRequest::AUTHORIZATION_BEARER`:  
   Bearer authentication. Requires a token.   
 - `HttpRequest::AUTHORIZATION_DIGEST`:  
   Digest authentication. Requires a token.

### Content type
#### `withContentType(string $contentType): HttpRequest`
Applies a content type to the request. If the content type is JSON, responses will be read as JSON, too. Multiple 
content types are available als class constants:
 - `HttpRequest::CONTENT_TYPE_JSON`:  
   `application/json`, for JSON bodies. Setting it will cause the request body to be encoded as JSON.
 - `HttpRequest::CONTENT_TYPE_FORM`:  
   `application/x-www-form-urlencoded`, for url encoded form submissions. *This is the default content type*.
 - `HttpRequest::CONTENT_TYPE_MULTIPART`:  
   `multipart/form-data`, for url encoded form submissions with binary attachments.
 - `HttpRequest::CONTENT_TYPE_TEXT`:  
   `text/plain`, for anything else.

#### `asJson([bool $asJson = true]): HttpRequest`
Shorthand method for 
[`withContentType(HttpRequest::CONTENT_TYPE_JSON)`](#withcontenttypestring-contenttype-httprequest).

#### `asBlob([bool $asBlob = true]): HttpRequest`
This has no effect currently but will be added in a future version to enable streaming binary files to a remote server.

### Redirects
#### `followRedirects([bool $followRedirects = true]): HttpRequest`
Shorthand method for `withCurlOption(CURLOPT_FOLLOWLOCATION, true)`

### Client SSL Certificate
### `withSSLClientCertificate(string $path, bool $force = false): HttpRequest`
Shorthand method for `withCurlOption(CURLOPT_SSLCERT, $path)`<BR>
If the second parameter is true there will be no check if the file exists.

### `withSSLClientCertificatePassword(string $password): HttpRequest`
Shorthand method for `withCurlOption(CURLOPT_SSLCERTPASSWD, $password)`

### `withSSLClientKey(string $path, bool $force = false): HttpRequest`
Shorthand method for `withCurlOption(CURLOPT_SSLKEY, $path)`<BR>
If the second parameter is true there will be no check if the file exists.

### `withSSLClientKeyPassword(string $password): HttpRequest`
Shorthand method for `withCurlOption(CURLOPT_SSLKEYPASSWD, $password)`

Todo list
---------
 - [ ] More tests
 - [ ] Blob transfers
