Bot Detection and Traffic Analysis Classes
==========================================

##  Description

This project tries to help identify suspicious traffic for logging or blocking. It provides some simple tools to determine if page requests are coming from natural traffic or if they are suspected as coming from automated scripts. The settings can be adjusted and users can be blocked by several fingerprints in their traffic including headers, page access speed, and if they are downloading all the resources the page references, such as CSS, Javascript and image files. Aside from page access speed, these classes are not entirely effective against browser automation.

This project has 3 parts that add a layer of protection to your content.

1. Logging.

2. Monitoring.

3. Blocking.

### Log Traffic

*   Log traffic and record the request information if it's a new visit.

        $traffix->log();

### Verifying the Referer Header

*   If you have a page that processes a request from a form on your site you can verify that the Referer Header was passed and that it is matches your page or site. Some bots won't pass this header and can be filtered out in this manner. Pass the assert_referer() function a part or all of the expected referer URL.

        $traffix->assert_referer('mysite.com');

### Assert Request Method

*   Verify that the request was passed using the expected method.

        $traffix->assert_request_method('POST');

### Require Request Headers

*   All major browsers reliably send certain headers that may be absent in traffic coming from automated sources. When a request is made missing one or all of these headers you may want to log the request as suspicious or deny the traffic.

        // These headers are checked for by default.
        Host
        Accept-Language
        User-Agent

*   You can check for a specific request header via the assert_request_header function. If you pass a second parameter the function will check if the value of the header matches it.

        $traffix->assert_request_header( 'MY_CUSTOM_HEADER' ); // returns true if exists

        $traffix->assert_request_header( 'HTTP_X_REQUESTED_WITH', 'xmlhttprequest' ); // returns true if exists & matches

### Track the Time Between Page Hits

*   Bots can crawl faster than humans can access pages on your site. Traffix keeps track of the page access speed of visitors.

        $traffix->pages_per_minute();


### Deny Traffic Based w/Rules and Blacklists

*   You can deny traffic by calling the deny() function. The deny function will check the request against your requirements and blacklists.

        $traffix->deny();

### Log Dependency File Downloads

*   Unless the bot is using browser automation, there is a good chance that it will not exhibit some of the same behaviors as legitimate traffic. The traffix class provides tools to make it easy to log if your visitors are downloading css, javascript, and image files. Monitoring this behavior can help to identify automated traffic that is sending false headers to appear as regular traffic. Even if a user is blocking javascript from executing, the actual javascript source file should still be downloaded by their browser.

        // CSS Monitoring: Call the css script from your HTML. [NOTE] You can hide the .php extension with .htaccess
        <link rel="stylesheet" type="text/css" href="monitored_css_file.php">
        
        // monitored_css_file.php
        <?php
        require '/path/to/classes/traffix.php';
        $traffix = new traffix;
        $traffix->monitor_css_file('/path/to/css_source_file.css');
        ?>

        
        // JS Monitoring: Call the js script from your HTML. [NOTE] You can hide the .php extension with .htaccess
        <script src="monitored_js_file.php"></script>
        
        // monitored_js_file.php
        <?php
        require '/path/to/classes/traffix.php';
        $traffix = new traffix;
        $traffix->monitor_js_file('/path/to/js_source_file.js');
        ?>
