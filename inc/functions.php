<?php

//---DATABASE FUNCTIONS---//

// Pull entires from the database
// TODO: Add use of limit and offet parameters for pagination
function getJournalEntries($limit = null, $offset = 0, $searchString = null) {
    try {
        include("inc/connection.php");  // include over require 
                                        // so we don't throw a fatal error and kill the whole page
        $searchString = strtolower($searchString);
        $sql = "SELECT * FROM entries";

        // A prepare statment is used in lieu of $db->query b/c
        // we'll be taking arguments to build our SQL query
        // that are derived from query string values; prepare() ensures
        // the query written here is what's truly executed

        // if there's a searchString, append the WHERE filter
        if(!empty($searchString)) {
            $searchString = "%" . $searchString . "%";  // SQL quotes automatically added by PDO per docs      
            $sql .= " WHERE title like :searchString1 
                    OR learned like :searchString2 
                    OR resources like :searchString3";

            // If a limit is specified apped it to the SQL else just use the offset
            // if limit is an integer, we know one is passed in and its not null
            if(is_integer($limit)) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $results = $db->prepare($sql);
                $results->bindParam(':searchString1', $searchString, PDO::PARAM_STR);
                $results->bindParam(':searchString2', $searchString, PDO::PARAM_STR);
                $results->bindParam(':searchString3', $searchString, PDO::PARAM_STR);
                $results->bindParam(':limit', $limit, PDO::PARAM_INT);
                $results->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            else {  // No limit specified
                $results = $db->prepare($sql);
                $results->bindParam(':searchString1', $searchString, PDO::PARAM_STR);
                $results->bindParam(':searchString2', $searchString, PDO::PARAM_STR);
                $results->bindParam(':searchString3', $searchString, PDO::PARAM_STR);
            }
        }
        else {
            if(is_integer($limit)) {    // Limit but no search query
                $sql .= " LIMIT :limit OFFSET :offset";
                $results = $db->prepare($sql);
                $results->bindParam(':limit', $limit, PDO::PARAM_INT);
                $results->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            else {  // No limit or search query
                $results = $db->prepare($sql);
            } 
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
function getJournalEntryCount($searchString = null) {
    include("inc/connection.php");
    try {
        $sql = "SELECT count(id)
                FROM entries";
        if(!empty($searchString)) {
            $searchString = "%" . $searchString . "%";  // SQL quotes automatically added by PDO per docs      
            $sql .= " WHERE title LIKE :searchString1 
                    OR learned LIKE :searchString2 
                    OR resources LIKE :searchString3";
            $results = $db->prepare($sql);
            $results->bindParam(':searchString1', $searchString, PDO::PARAM_STR);
            $results->bindParam(':searchString2', $searchString, PDO::PARAM_STR);
            $results->bindParam(':searchString3', $searchString, PDO::PARAM_STR);
        }
        else {
            $results = $db->prepare($sql);
        }
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

//--VIEW FUNCTIONS--//

function renderPaginationLinks($currentPage, $totalPages, $searchString = null)
{
    // Display links for pagination
    // If page number link matches current page disable the link
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            echo "<li>" . $i . "</li>";
        } else {
            if (!empty($searchString)) {
                echo "<li><a href=\"index.php?page=" . $i . "&searchQuery=" . $searchString . "\">" . $i . "</a></li>";
            } else {
                echo "<li><a href=\"index.php?page=" . $i . "\">" . $i . "</a></li>";
            }
        }
    }
}
