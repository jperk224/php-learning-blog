<?php

// Enable error reporting; 
//*********REMOVE THIS BEFORE PRODUCTION PROMOTION**********
ini_set('display_errors', 'On');  // Display all errors for debuggin purposes REMOVE BEFORE PRODUCTION

// Instantiate a PDO object to connect to the DB (Connects to local SQLite DB)
// Connection string components
$dsn = "sqlite:inc/journal.db";  // Data Source Name, local db in same file

// Wrap in try-catch to cleanly display exceptions
try {
    $db = new PDO($dsn);
    // throw an exception for every PDO object error, don't silence or warn
    // helps with debugging
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e) {                      // Use the Exception parent class to catch all exceptions thrown (should all be PDO exceptions here)
    echo $e->getMessage() . "<br>";                  // Render the error message
    die("Uh oh, something is wrong...");   // Stop the scriot if an exception is caught (use die to render a friendly message)
}
