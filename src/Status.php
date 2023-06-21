<?php /** @noinspection PhpDeprecationInspection */

namespace CodeWorx\Http;

use function is_int;

class Status
{
    /**
     * The request has been accepted for processing, but the processing has not been completed. The request might or
     * might not be eventually acted upon, and may be disallowed when processing occurs.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/202
     */
    public const ACCEPTED = 202;

    /**
     * The members of a DAV binding have already been enumerated in a preceding part of the (multi status) response,
     * and are not being included again.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/208
     */
    public const ALREADY_REPORTED = 208;

    /**
     * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/502
     */
    public const BAD_GATEWAY = 502;

    /**
     * The server cannot or will not process the request due to an apparent client error (e.g., malformed request
     * syntax, size too large, invalid request message framing, or deceptive request routing).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/400
     */
    public const BAD_REQUEST = 400;

    /**
     * The server has exceeded the bandwidth specified by the server administrator; this is often used by shared
     * hosting providers to limit the bandwidth of customers.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/509
     */
    public const BANDWIDTH_LIMIT_EXCEEDED = 509;

    /**
     * Used when the client has closed the request before the server could send a response.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/499
     */
    public const CLIENT_CLOSED_REQUEST = 499;

    /**
     * This class of status code is intended for situations in which the error seems to have been caused by the client.
     * Except when responding to a HEAD request, the server should include an entity containing an explanation of the
     * error situation, and whether it is a temporary or permanent condition. These status codes are applicable to any
     * request method. User agents should display any included entity to the user.
     */
    public const CLIENT_ERROR_CODES = [
        self::BAD_REQUEST,
        self::UNAUTHORIZED,
        self::PAYMENT_REQUIRED,
        self::FORBIDDEN,
        self::NOT_FOUND,
        self::METHOD_NOT_ALLOWED,
        self::NOT_ACCEPTABLE,
        self::PROXY_AUTHENTICATION_REQUIRED,
        self::REQUEST_TIMEOUT,
        self::CONFLICT,
        self::GONE,
        self::LENGTH_REQUIRED,
        self::PRECONDITION_FAILED,
        self::PAYLOAD_TOO_LARGE,
        self::URI_TOO_LONG,
        self::UNSUPPORTED_MEDIA_TYPE,
        self::RANGE_NOT_SATISFIABLE,
        self::EXPECTATION_FAILED,
        self::IM_A_TEAPOT,
        self::PAGE_EXPIRED,
        self::MISDIRECTED_REQUEST,
        self::UNPROCESSABLE_ENTITY,
        self::LOCKED,
        self::FAILED_DEPENDENCY,
        self::UPGRADE_REQUIRED,
        self::PRECONDITION_REQUIRED,
        self::TOO_MANY_REQUESTS,
        self::REQUEST_HEADER_FIELDS_TOO_LARGE,
        self::UNAVAILABLE_FOR_LEGAL_REASONS,
        self::HTTP_REQUEST_SENT_TO_HTTPS_PORT,
        self::CLIENT_CLOSED_REQUEST,
    ];

    /**
     * Indicates that the request could not be processed because of conflict in the current state of the resource, such
     * as an edit conflict between multiple simultaneous updates.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/409
     */
    public const CONFLICT = 409;

    /**
     * The HTTP 100 Continue informational status response code indicates that everything so far is OK and that the
     * client should continue with the request or ignore it if it is already finished.
     *
     * To have a server check the request's headers, a client must send Expect: 100-continue as a header in its initial
     * request and receive a 100 Continue status code in response before sending the body.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/100
     */
    public const CONTINUE = 100;

    /**
     * The request has been fulfilled, resulting in the creation of a new resource.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/201
     */
    public const CREATED = 201;

    /**
     * Used to return some response headers before final HTTP message.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/103
     */
    public const EARLY_HINTS = 103;

    public const ERROR_CODES = [
        self::BAD_REQUEST,
        self::UNAUTHORIZED,
        self::PAYMENT_REQUIRED,
        self::FORBIDDEN,
        self::NOT_FOUND,
        self::METHOD_NOT_ALLOWED,
        self::NOT_ACCEPTABLE,
        self::PROXY_AUTHENTICATION_REQUIRED,
        self::REQUEST_TIMEOUT,
        self::CONFLICT,
        self::GONE,
        self::LENGTH_REQUIRED,
        self::PRECONDITION_FAILED,
        self::PAYLOAD_TOO_LARGE,
        self::URI_TOO_LONG,
        self::UNSUPPORTED_MEDIA_TYPE,
        self::RANGE_NOT_SATISFIABLE,
        self::EXPECTATION_FAILED,
        self::IM_A_TEAPOT,
        self::PAGE_EXPIRED,
        self::MISDIRECTED_REQUEST,
        self::UNPROCESSABLE_ENTITY,
        self::LOCKED,
        self::FAILED_DEPENDENCY,
        self::UPGRADE_REQUIRED,
        self::PRECONDITION_REQUIRED,
        self::TOO_MANY_REQUESTS,
        self::REQUEST_HEADER_FIELDS_TOO_LARGE,
        self::UNAVAILABLE_FOR_LEGAL_REASONS,
        self::HTTP_REQUEST_SENT_TO_HTTPS_PORT,
        self::CLIENT_CLOSED_REQUEST,
        self::INTERNAL_SERVER_ERROR,
        self::NOT_IMPLEMENTED,
        self::BAD_GATEWAY,
        self::HTTP_VERSION_NOT_SUPPORTED,
        self::VARIANT_ALSO_NEGOTIATES,
        self::INSUFFICIENT_STORAGE,
        self::LOOP_DETECTED,
        self::BANDWIDTH_LIMIT_EXCEEDED,
        self::NOT_EXTENDED,
        self::NETWORK_AUTHENTICATION_REQUIRED,
        self::INVALID_SSL_CERTIFICATE,
        self::SITE_IS_FROZEN,
        self::NETWORK_READ_TIMEOUT_ERROR,
    ];

    /**
     * The server cannot meet the requirements of the Expect request-header field.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/417
     */
    public const EXPECTATION_FAILED = 417;

    /**
     * The request failed because it depended on another request and that request failed (e.g., a PROPPATCH).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/424
     */
    public const FAILED_DEPENDENCY = 424;

    /**
     * The request was valid, but the server is refusing action. The user might not have the necessary permissions for
     * a resource, or may need an account of some sort.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/403
     */
    public const FORBIDDEN = 403;

    /**
     * Tells the client to look at (browse to) another url. 302 has been superseded by 303 and 307. This is an example
     * of industry practice contradicting the standard. The HTTP/1.0 specification (RFC 1945) required the client to
     * perform a temporary redirect (the original describing phrase was "Moved Temporarily"), but popular browsers
     * implemented 302 with the functionality of a 303 See Other. Therefore, HTTP/1.1 added status codes 303 and 307 to
     * distinguish between the two behaviours. However, some Web applications and frameworks use the 302 status code as
     * if it were the 303.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/302
     */
    public const FOUND = 302;

    /**
     * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/504
     */
    public const GATEWAY_TIMEOUT = 504;

    /**
     * Indicates that the resource requested is no longer available and will not be available again. This should be
     * used when a resource has been intentionally removed and the resource should be purged. Upon receiving a 410
     * status code, the client should not request the resource in the future. Clients such as search engines should
     * remove the resource from their indices. Most use cases do not require clients and search engines to purge the
     * resource, and a "404 Not Found" may be used instead.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/410
     */
    public const GONE = 410;

    /**
     * An expansion of the 400 Bad Request response code, used when the client has made a HTTP request to a port
     * listening for HTTPS requests.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/497
     */
    public const HTTP_REQUEST_SENT_TO_HTTPS_PORT = 497;

    /**
     * The server does not support the HTTP protocol version used in the request.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/505
     */
    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * This code was defined in 1998 as one of the traditional IETF April Fools' jokes, in RFC 2324, Hyper Text Coffee
     * Pot Control Protocol, and is not expected to be implemented by actual HTTP servers. The RFC specifies this code
     * should be returned by teapots requested to brew coffee. This HTTP status is used as an Easter egg in some
     * websites, including Google.com.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/418
     */
    public const IM_A_TEAPOT = 418;

    /**
     * The server has fulfilled a request for the resource, and the response is a representation of the result of one
     * or more instance-manipulations applied to the current instance.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/226
     */
    public const IM_USED = 226;

    /**
     * An informational response indicates that the request was received and understood. It is issued on a provisional
     * basis while request processing continues. It alerts the client to wait for a final response. The message
     * consists only of the status line and optional header fields, and is terminated by an empty line. As the HTTP/1.0
     * standard did not define any 1xx status codes, servers must not send a 1xx response to an HTTP/1.0 compliant
     * client except under experimental conditions.
     */
    public const INFORMATIONAL_CODES = [
        self::CONTINUE,
        self::SWITCHING_PROTOCOLS,
        self::PROCESSING,
        self::EARLY_HINTS,
    ];

    /**
     * The server is unable to store the representation needed to complete the request.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/507
     */
    public const INSUFFICIENT_STORAGE = 507;

    /**
     * A generic error message, given when an unexpected condition was encountered and no more specific message is
     * suitable.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/500
     */
    public const INTERNAL_SERVER_ERROR = 500;

    /**
     * Used by CloudFlare and Cloud Foundry's gorouter to indicate failure to validate the SSL/TLS certificate that the
     * origin server presented.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/526
     */
    public const INVALID_SSL_CERTIFICATE = 526;

    /**
     * The request did not specify the length of its content, which is required by the requested resource.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/411
     */
    public const LENGTH_REQUIRED = 411;

    /**
     * The resource that is being accessed is locked.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/423
     */
    public const LOCKED = 423;

    /**
     * The server detected an infinite loop while processing the request (sent instead of 208 Already Reported).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/508
     */
    public const LOOP_DETECTED = 508;

    /**
     * A request method is not supported for the requested resource; for example, a GET request on a form that requires
     * data to be presented via POST, or a PUT request on a read-only resource.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/405
     */
    public const METHOD_NOT_ALLOWED = 405;

    /**
     * The request was directed at a server that is not able to produce a response (for example because of connection
     * reuse).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/421
     */
    public const MISDIRECTED_REQUEST = 421;

    /**
     * This and all future requests should be directed to the given URI.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/301
     */
    public const MOVED_PERMANENTLY = 301;

    /**
     * Indicates multiple options for the resource from which the client may choose (via agent-driven content
     * negotiation). For example, this code could be used to present multiple video format options, to list files with
     * different filename extensions, or to suggest word-sense disambiguation.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/300
     */
    public const MULTIPLE_CHOICES = 300;

    /**
     * The message body that follows is by default an XML message and can contain a number of separate response codes,
     * depending on how many sub-requests were made.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/207
     */
    public const MULTI_STATUS = 207;

    /**
     * The client needs to authenticate to gain network access. Intended for use by intercepting proxies used to
     * control access to the network (e.g., "captive portals" used to require agreement to Terms of Service before
     * granting full Internet access via a Wi-Fi hot-spot).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/511
     */
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * Used by some HTTP proxies to signal a network read timeout behind the proxy to a client in front of the proxy.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/598
     */
    public const NETWORK_READ_TIMEOUT_ERROR = 598;

    /**
     * The server is a transforming proxy (e.g. a Web accelerator) that received a 200 OK from its origin, but is
     * returning a modified version of the origin's response.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/203
     */
    public const NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The requested resource is capable of generating only content not acceptable according to the Accept headers sent
     * in the request. See Content negotiation.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/406
     */
    public const NOT_ACCEPTABLE = 406;

    /**
     * Further extensions to the request are required for the server to fulfil it.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/510
     */
    public const NOT_EXTENDED = 510;

    /**
     * The requested resource could not be found but may be available in the future. Subsequent requests by the client
     * are permissible.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/404
     */
    public const NOT_FOUND = 404;

    /**
     * The server either does not recognize the request method, or it lacks the ability to fulfil the request. Usually
     * this implies future availability (e.g., a new feature of a web-service API).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/501
     */
    public const NOT_IMPLEMENTED = 501;

    /**
     * Indicates that the resource has not been modified since the version specified by the request headers
     * `If-Modified-Since` or
     * `If-None-Match`. In such case, there is no need to retransmit the resource since the client still has a
     * previously-downloaded copy.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/304
     */
    public const NOT_MODIFIED = 304;

    /**
     * The server successfully processed the request and is not returning any content.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/204
     */
    public const NO_CONTENT = 204;

    /**
     * Standard response for successful HTTP requests. The actual response will depend on the request method used. In a
     * GET request, the response will contain an entity corresponding to the requested resource. In a POST request, the
     * response will contain an entity describing or containing the result of the action.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/200
     */
    public const OK = 200;

    /**
     * Used by the Laravel Framework when a CSRF Token is missing or expired.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/419
     */
    public const PAGE_EXPIRED = 419;

    /**
     * The server is delivering only part of the resource (byte serving) due to a range header sent by the client. The
     * range header is used by HTTP clients to enable resuming of interrupted downloads, or split a download into
     * multiple simultaneous streams.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/206
     */
    public const PARTIAL_CONTENT = 206;

    /**
     * The request is larger than the server is willing or able to process. Previously called "Request Entity Too
     * Large".
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/413
     */
    public const PAYLOAD_TOO_LARGE = 413;

    /**
     * Reserved for future use. The original intention was that this code might be used as part of some form of digital
     * cash or micro-payment scheme, as proposed for example by GNU Taler, but that has not yet happened, and this code
     * is not usually used. Google Developers API uses this status if a particular developer has exceeded the daily
     * limit on requests. Sipgate uses this code if an account does not have sufficient funds to start a call. Shopify
     * uses this code when the store has not paid their fees and is temporarily disabled.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/402
     */
    public const PAYMENT_REQUIRED = 402;

    /**
     * The request and all future requests should be repeated using another URI. 307 and 308 parallel the behaviors of
     * 302 and 301, but do not allow the HTTP method to change. So, for example, submitting a form to a permanently
     * redirected resource may continue smoothly.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/308
     */
    public const PERMANENT_REDIRECT = 308;

    /**
     * The server does not meet one of the preconditions that the requester put on the request.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/412
     */
    public const PRECONDITION_FAILED = 412;

    /**
     * The origin server requires the request to be conditional. Intended to prevent the 'lost update' problem, where a
     * client GETs a resource's state, modifies it, and PUTs it back to the server, when meanwhile a third party has
     * modified the state on the server, leading to a conflict."
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/428
     */
    public const PRECONDITION_REQUIRED = 428;

    /**
     * A WebDAV request may contain many sub-requests involving file operations, requiring a long time to complete the
     * request. This code indicates that the server has received and is processing the request, but no response is
     * available yet. This prevents the client from timing out and assuming the request was lost.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/102
     */
    public const PROCESSING = 102;

    /**
     * The client must first authenticate itself with the proxy.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/407
     */
    public const PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * The client has asked for a portion of the file (byte serving), but the server cannot supply that portion. For
     * example, if the client asked for a part of the file that lies beyond the end of the file. Called "Requested
     * Range Not Satisfiable" previously.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/416
     */
    public const RANGE_NOT_SATISFIABLE = 416;

    /**
     * This class of status code indicates the client must take additional action to complete the request. Many of
     * these status codes are used in URL redirection.
     *
     * A user agent may carry out the additional action with no user interaction only if the method used in the second
     * request is GET or HEAD. A user agent may automatically redirect a request. A user agent should detect and
     * intervene to prevent cyclical redirects.
     */
    public const REDIRECTION_CODES = [
        self::MULTIPLE_CHOICES,
        self::MOVED_PERMANENTLY,
        self::FOUND,
        self::SEE_OTHER,
        self::NOT_MODIFIED,
        self::USE_PROXY,
        self::SWITCH_PROXY,
        self::TEMPORARY_REDIRECT,
        self::PERMANENT_REDIRECT,
    ];

    /**
     * The server is unwilling to process the request because either an individual header field, or all the header
     * fields collectively, are too large.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/431
     */
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

    /**
     * Client sent too large request or too long header line.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/494
     */
    public const REQUEST_HEADER_TOO_LARGE = 494;

    /**
     * The server timed out waiting for the request. According to HTTP specifications: "The client did not produce a
     * request within the time that the server was prepared to wait. The client MAY repeat the request without
     * modifications at any later time."
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/408
     */
    public const REQUEST_TIMEOUT = 408;

    /**
     * The server successfully processed the request, but is not returning any content. Unlike a 204 response, this
     * response requires that the requester reset the document view.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/205
     */
    public const RESET_CONTENT = 205;

    /**
     * The response to the request can be found under another URI using the GET method. When received in response to a
     * POST (or PUT/DELETE), the client should presume that the server has received the data and should issue a new GET
     * request to the given URI.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/303
     */
    public const SEE_OTHER = 303;

    /**
     * The server failed to fulfil a request.
     *
     * Response status codes beginning with the digit "5" indicate cases in which the server is aware that it has
     * encountered an error or is otherwise incapable of performing the request. Except when responding to a HEAD
     * request, the server should include an entity containing an explanation of the error situation, and indicate
     * whether it is a temporary or permanent condition. Likewise, user agents should display any included entity to
     * the user. These response codes are applicable to any request method.
     */
    public const SERVER_ERROR_CODES = [
        self::INTERNAL_SERVER_ERROR,
        self::NOT_IMPLEMENTED,
        self::BAD_GATEWAY,
        self::HTTP_VERSION_NOT_SUPPORTED,
        self::VARIANT_ALSO_NEGOTIATES,
        self::INSUFFICIENT_STORAGE,
        self::LOOP_DETECTED,
        self::BANDWIDTH_LIMIT_EXCEEDED,
        self::NOT_EXTENDED,
        self::NETWORK_AUTHENTICATION_REQUIRED,
        self::INVALID_SSL_CERTIFICATE,
        self::SITE_IS_FROZEN,
        self::NETWORK_READ_TIMEOUT_ERROR,
    ];

    /**
     * The server is currently unavailable (because it is overloaded or down for maintenance). Generally, this is a
     * temporary state.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/503
     */
    public const SERVICE_UNAVAILABLE = 503;

    /**
     * Used by the Pantheon web platform to indicate a site that has been frozen due to inactivity.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/530
     */
    public const SITE_IS_FROZEN = 530;

    /**
     * This class of status codes indicates the action requested by the client was received, understood and accepted.
     */
    public const SUCCESS_CODES = [
        self::OK,
        self::CREATED,
        self::ACCEPTED,
        self::NON_AUTHORITATIVE_INFORMATION,
        self::NO_CONTENT,
        self::RESET_CONTENT,
        self::PARTIAL_CONTENT,
        self::MULTI_STATUS,
        self::ALREADY_REPORTED,
        self::IM_USED,
    ];

    /**
     * The HTTP 101 Switching Protocols response code indicates the protocol the server is switching to as requested by
     * a client which sent the message including the Upgrade request header. The server includes in this response an
     * Upgrade response header to indicate the protocol it switched to. The process is described in detail in the
     * article Protocol upgrade mechanism.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/101
     */
    public const SWITCHING_PROTOCOLS = 101;

    /**
     * No longer used. Originally meant "Subsequent requests should use the specified proxy."
     *
     * @deprecated
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/306
     */
    public const SWITCH_PROXY = 306;

    /**
     * In this case, the request should be repeated with another URI; however, future requests should still use the
     * original URI. In contrast to how 302 was historically implemented, the request method is not allowed to be
     * changed when reissuing the original request. For example, a POST request should be repeated using another POST
     * request.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/307
     */
    public const TEMPORARY_REDIRECT = 307;

    /**
     * The user has sent too many requests in a given amount of time. Intended for use with rate-limiting schemes.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/429
     */
    public const TOO_MANY_REQUESTS = 429;

    /**
     * Similar to 403 Forbidden, but specifically for use when authentication is required and has failed or has not yet
     * been provided. The response must include a WWW-Authenticate header field containing a challenge applicable to
     * the requested resource. See Basic access authentication and Digest access authentication. 401 semantically means
     * "unauthenticated", i.e. the user does not  have the necessary credentials. Note: Some sites incorrectly issue
     * HTTP 401 when an IP address is banned from the website (usually the website domain) and that specific address is
     * refused permission to access a website.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/401
     */
    public const UNAUTHORIZED = 401;

    /**
     * A server operator has received a legal demand to deny access to a resource or to a set of resources that
     * includes the requested resource. The code 451 was chosen as a reference to the novel Fahrenheit 451 (see the
     * Acknowledgements in the RFC).
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/451
     */
    public const UNAVAILABLE_FOR_LEGAL_REASONS = 451;

    /**
     * The request was well-formed but was unable to be followed due to semantic errors.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/422
     */
    public const UNPROCESSABLE_ENTITY = 422;

    /**
     * The request entity has a media type which the server or resource does not support. For example, the client
     * uploads an image as image/svg+xml, but the server requires that images use a different format.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/415
     */
    public const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * The client should switch to a different protocol such as TLS/1.0, given in the Upgrade header field.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/426
     */
    public const UPGRADE_REQUIRED = 426;

    /**
     * The URI provided was too long for the server to process. Often the result of too much data being encoded as a
     * query-string of a GET request, in which case it should be converted to a POST request. Called "Request-URI Too
     * Long" previously.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/414
     */
    public const URI_TOO_LONG = 414;

    /**
     * The requested resource is available only through a proxy, the address for which is provided in the response.
     * Many HTTP clients
     * (such as Mozilla Firefox and Internet Explorer) do not correctly handle responses with this status code,
     * primarily for security reasons.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/305
     */
    public const USE_PROXY = 305;

    /**
     * Transparent content negotiation for the request results in a circular reference.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/506
     */
    public const VARIANT_ALSO_NEGOTIATES = 506;

    /**
     * WebDAV related status codes
     */
    public const WEBDAV_CODES = [
        self::PROCESSING,
        self::MULTI_STATUS,
        self::ALREADY_REPORTED,
        self::UNPROCESSABLE_ENTITY,
        self::LOCKED,
        self::FAILED_DEPENDENCY,
        self::INSUFFICIENT_STORAGE,
        self::LOOP_DETECTED,
    ];

    /**
     * Holds all status messages
     *
     * @var array
     */
    private static $messages = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        419 => 'Page Expired',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        497 => 'HTTP Request Sent to HTTPS Port',
        499 => 'Client Closed Request',

        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
        526 => 'Invalid SSL Certificate',
        530 => 'Site is frozen',
        598 => 'Network read timeout error',
    ];

    /**
     * Retrieves the status message for an status code
     *
     * @param int $code Status code
     *
     * @return string
     */
    final public static function getMessage(int $code): string
    {
        return static::$messages[$code] ?? '';
    }

    /**
     * Retrieves the header line for a status code
     *
     * @param int       $code        Status code
     * @param int|float $httpVersion Optional HTTP version. Defaults to 1.1
     *
     * @return string
     * @example Status::getHeaderLine(200, 1.1); // 'HTTP/1.1 200 OK'
     */
    final public static function getHeaderLine(int $code, float $httpVersion = 1.1): string
    {
        $message = static::getMessage($code);

        return "HTTP/$httpVersion $code $message";
    }

    /**
     * Checks whether a status code is an error
     *
     * @param int $code Status code
     *
     * @return bool
     */
    final public static function isErrorCode(int $code): bool
    {
        return is_int($code) && $code >= static::BAD_REQUEST;
    }
}
