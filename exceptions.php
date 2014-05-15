<?php
/****
 * EXCEPTIONS.PHP
 * Setup handlers for unprocessed errors and exceptions
 * 
 * Handlers
 * OnError, OnException
 * 
 * Globals
 * $page    page to return to user after reporting error. The created error
 *          will be in session variable "Fatal_Error".
 * 
 * Terence Lein, tlein@optonline.net
 * 
 *****/

set_error_handler('OnError');
set_exception_handler('OnException');

// unhandled errors: convert error to exception
function OnError($errno, $errstr, $errfile, $errline) {
    
  // ignorable errors; uncomment the ones to ignore. The rest will
  // be reported as errors.
  switch($errno){
//  case E_ERROR :
//  case E_WARNING :
//  case E_PARSE :
  case E_NOTICE:
//  case E_CORE_ERROR:
//  case E_CORE_WARNING:
//  case E_COMPILE_ERROR:
//  case E_COMPILE_WARNING:
//  case E_USER_ERROR:
//  case E_USER_WARNING:
  case E_USER_NOTICE:
  case E_STRICT:
//  case E_RECOVERABLE_ERROR:
//  case E_DEPRECATED:
//  case E_USER_DEPRECATED:
    return TRUE;
  }
  throw new ErrorException($errstr,$errno,0,$errfile,$errline);
}

// unhandled exceptions; build error message and send to admin
function OnException($exception) {
  $code = $exception->getCode();
  $msg  = sprintf("\n\nUnhandled exception: (%d) %s in file %s at line %s\n\n%s\n",
                  $code,
                  $exception->getMessage(),
                  $exception->getFile(),
                  $exception->getLine(),
                  $exception->getTraceAsString()
                  );
  ErrorMail ($msg);
  exit;
}

// send email to admin; include session variables for debugging
function ErrorMail ($msg) {
  global $page;

  if(isset( $_GET["pw"]))     $_GET["pw"] == "*"; // hide password  
  if(isset($_POST["pw"]))    $_POST["pw"] == "*"; // hide password 
  $msg  = sprintf("%s%s%s%s%s",
                  $msg,
                  (isset($_GET))
                  ? sprintf("\nGET=>%s",print_r($_GET,true)) : "",
                  (isset($_POST))
                  ? sprintf("\nPOST=>%s",print_r($_POST,true)) : "",
                  (isset($_FILES))
                  ? sprintf("\nFILES=>%s",print_r($_FILES,true)) : "",
                  (isset($_SESSION))
                  ? sprintf("\nSESSION=>%s",print_r($_SESSION,true)) : "",
                  sprintf("\nSERVER=>%s",print_r($_SERVER,true)) );

  if($_SESSION["Debug"] != "Yes"){
    $email  = $_SERVER["SERVER_ADMIN"];
    mail ($email,                         // recipient email
            $_SERVER["SERVER_NAME"]." Error",          // subject
            $msg,                           // message body
            "From: $email\n" .              // header: from
            "Reply-To: $email\n",           // header: reply to
            "-f $email");                   // parameters
    $_SESSION["Fatal_Error"] =
        "An error has occurred and has been reported to the system ".
        "administrator. Sorry for the inconvenience.<br>";
    header("Location: $page");
  } else {
    echo nl2br($msg);
  }
}


?>
