<?php

//---DATABASE FUNCTIONS---//

// Pull entires from the database
// TODO: Add use of limit and offet parameters for pagination
function getJournalEntries($limit = null, $offset = null) {
    try {
        include("inc/connection.php");  // include over require 
                                        // so we don't throw a fatal error and kill the whole page
        $sql = "SELECT * FROM entries";
        // If an offset is specific append the SQL to include it
        // if(!empty($offset)) {
        //     $sql .= " OFFSET ?"
        // }

        // A prepare statment is used in lieu of $db->query b/c
        // we'll be taking values from the url query string as function
        // arguments to build our SQL query; prepare() ensures
        // the query written here is what's executed and prevents SQL injection
        $results = $db->prepare($sql);
        //TODO: bind parameters
        $results->execute();
    }
    catch (Exception $e) {
        echo $e->getMessage();
        die();          // kill the script if it can't pull from the DB 
    }                   // and stop remainder of page from loading
    
    // return the results in an associative array so we can leverage column-named keys
    return $results->fetchAll(PDO::FETCH_ASSOC);
}
