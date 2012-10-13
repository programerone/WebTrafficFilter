traffix -- A Bot Detection Class and Traffic Analyzer
=========================================================

##  Description

This class tries to help identify suspicious traffic for logging or blocking. It provides some simple tools to determine if page requests are coming from natural traffic or if they are suspected as coming from automated scripts.

##  traffic.php traffic class methods

The traffic class 

### Verifying the Referer Header

*   If you have a page that processes a request from a form on your site you can verify that the Referer Header was passed and that it is matches your page or site. Some bots won't pass this header and can be filtered out in this manner. Pass the assert_referer() function a part or all of the expected referer URL.

        $traffix->assert_referer('mysite.com');

### Assert Request Method

*   Verify that the request was passed using the expected method.

        $traffix->assert_request_method('POST');
        
### Require Request Headers

*   All major browsers reliably send certain headers that may be absent in traffic coming from automated sources. When a request is made missing one or all of these headers you may want to log the request as suspicious or deny the traffic.

        // These headers are required by default
        Host
        Accept-Language
        User-Agent

*   You can check for a specific header via the assert_request_header function.

        $traffix->assert_request_header( 'HTTP_X_REQUESTED_WITH', 'xmlhttprequest' );
        
## Simple Example: Log initial requests.

