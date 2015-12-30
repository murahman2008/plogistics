<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
define('SHOW_DEBUG_BACKTRACE', TRUE);

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
define('EXIT_SUCCESS', 0); // no errors
define('EXIT_ERROR', 1); // generic error
define('EXIT_CONFIG', 3); // configuration error
define('EXIT_UNKNOWN_FILE', 4); // file not found
define('EXIT_UNKNOWN_CLASS', 5); // unknown class
define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
define('EXIT_USER_INPUT', 7); // invalid user input
define('EXIT_DATABASE', 8); // database error
define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/*********************** custom constants *********************************/

/* PRODUCT INSTANCE AVAILABLE STATUS */
define('PRODUCT_AVAILABLE', 1);
define('PRODUCT_UNAVAILABLE', 2);
define('PRODUCT_BOOKED', 3);

/* SELL SOURCE */
define('SELL_SOURCE_EBAY', 1);
define('SELL_SOURCE_WEBSITE', 2);
define('SELL_SOURCE_FACEBOOK', 4);

/* SELL ORDER STATUS*/
define("SELL_ORDER_STATUS_AWAITING_PAYMENT", 1);
define("SELL_ORDER_STATUS_AWAITING_POSTAGE", 2);
define("SELL_ORDER_STATUS_AWAITING_POSTAGE_COS", 3);
define("SELL_ORDER_STATUS_DISPATCHED", 4);

/* PAYMENT METHOD */
define("PAYMENT_METHOD_CASH", 1);
define("PAYMENT_METHOD_CREDIT_CARD", 2);
define("PAYMENT_METHOD_PAYPAL", 3);
define("PAYMENT_METHOD_OTHER", 4);

/* DELIVERY METHOD */
define("DELIVERY_METHOD_DELIVERY", 1);
define("DELIVERY_METHOD_WAREHOUSE_PICKUP", 2);
define("DELIVERY_METHOD_STORE_PICKUP", 3);

define("FILE_UPLOAD_TYPE_EBAY", "1");
define("FILE_UPLOAD_TYPE_WEBSITE", "2");
define("FILE_UPLOAD_TYPE_OTHER", "3");

define("GST_AMOUNT", 15);
define("BOOKING_EXPIRY_DURATION_SEC", 1800);

define("EBAY_COLUMN_COUNT", 45);
define('BARCODE_PREFIX', 'OL');
define('BARCODE_NUMBER_LENGTH', 20);

define("WAREHOUSE_CODE_TEMP_HOLDER", "TEMP_HOLDER");

define("WAREHOUSE_TYPE_CENTRAL_WAREHOUSE", 1);
define("WAREHOUSE_TYPE_COUNTRY_WAREHOUSE", 2);
define("WAREHOUSE_TYPE_STATE_WAREHOUSE", 3);
define("WAREHOUSE_TYPE_NORMAL_WAREHOUSE", 4);
define("WAREHOUSE_TYPE_VIRTUAL_WAREHOUSE", 5);

define("DEFAULT_PAGE_SIZE", 2);

/*****************************************************************************/
