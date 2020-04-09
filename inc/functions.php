<?php

//---DATABASE FUNCTIONS---//

// Pull entires from the database
// TODO: Add use of limit and offet parameters for pagination
function getJournalEntries($limit = null, $offset = 0) {
    try {
        include("inc/connection.php");  // include over require 
                                        // so we don't throw a fatal error and kill the whole page
        $sql = "SELECT * FROM entries";

        // A prepare statment is used in lieu of $db->query b/c
        // we'll be taking arguments to build our SQL query
        // that are derived from query string values; prepare() ensures
        // the query written here is what's truly executed

        // If a limit is specified apped it to the SQL else just use the offset
        // if limit is an integer, we know one is passed in and its not null
        if(is_integer($limit)) {
            $sql .= " LIMIT ? OFFSET ?";
            $results = $db->prepare($sql);
            $results->bindParam(1, $limit, PDO::PARAM_INT);
            $results->bindParam(2, $offset, PDO::PARAM_INT);
        }
        // If no limit argument, return all values
        else {
            $results = $db->prepare($sql);
        }       
        $results->execute();
    }
    catch (Exception $e) {
        echo $e->getMessage();
        die();          // kill the script if it can't pull from the DB 
    }                   // and stop remainder of page from loading
    
    // return the results in an associative array so we can leverage column-named keys
    return $results->fetchAll(PDO::FETCH_ASSOC);
}

// For pagination to work, we need to know how many entries are in the database
// this function counts the number of entires in the entries table by id
function getJournalEntryCount() {
    include("inc/connection.php");
    try {
        $sql = "SELECT count(id)
                FROM entries";
        $results = $db->prepare($sql);
        $results->execute();        
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }

    // our SQL query will return only a single column result with a single row
    // use fetchColumn index 0 to grab the first column from the result set
    $count = $results->fetchColumn(0);  

    return $count;
}
