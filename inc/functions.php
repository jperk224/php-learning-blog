<?php

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//---DATABASE FUNCTIONS---//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Pull entires from the database
function getJournalEntries($limit = null, $offset = 0, $searchString = null) {
    try {
        include("inc/connection.php");  // include over require 
                                        // so we don't throw a fatal error and kill the whole page
        $searchString = strtolower($searchString);   // ignore case; maybe overkill b/c SQL LIKE ignores case
        $sql = "SELECT * FROM entries";

        // A prepare statment is used in lieu of $db->query b/c
        // we'll be taking arguments to build our SQL query
        // that are derived from query string values; prepare() ensures
        // the query written here is what's truly executed

        // if there's a searchString, append the WHERE filter
        if(!empty($searchString)) {
            $searchString = "%" . $searchString . "%";  // SQL quotes automatically added by PDO per docs      
            $sql .= " WHERE title like :searchString1 
                    OR learned like :searchString2";

            // If a limit is specified apped it to the SQL else just use the offset
            // if limit is an integer, we know one is passed in and its not null
            if(is_integer($limit)) {
                // append order by date
                $sql .= " ORDER BY `date` DESC";
                $sql .= " LIMIT :limit OFFSET :offset";
                $results = $db->prepare($sql);
                $results->bindParam(':searchString1', $searchString, PDO::PARAM_STR);
                $results->bindParam(':searchString2', $searchString, PDO::PARAM_STR);
                $results->bindParam(':limit', $limit, PDO::PARAM_INT);
                $results->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            else {  // No limit specified
                // append order by date
                $sql .= " ORDER BY `date` DESC";
                $results = $db->prepare($sql);
                $results->bindParam(':searchString1', $searchString, PDO::PARAM_STR);
                $results->bindParam(':searchString2', $searchString, PDO::PARAM_STR);
            }
        }
        else {
            if(is_integer($limit)) {    // Limit but no search query
                // append order by date
                $sql .= " ORDER BY `date` DESC";
                $sql .= " LIMIT :limit OFFSET :offset";
                $results = $db->prepare($sql);
                $results->bindParam(':limit', $limit, PDO::PARAM_INT);
                $results->bindParam(':offset', $offset, PDO::PARAM_INT);
            }
            else {  // No limit or search query
                // append order by date
                $sql .= " ORDER BY `date` DESC";
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
    $searchString = strtolower($searchString);
    try {
        $sql = "SELECT COUNT(id)
                FROM entries";
        if(!empty($searchString)) {
            $searchString = "%" . $searchString . "%";  // SQL quotes automatically added by PDO per docs      
            $sql .= " WHERE title LIKE :searchString1 
                    OR learned LIKE :searchString2";
            $results = $db->prepare($sql);
            $results->bindParam(':searchString1', $searchString, PDO::PARAM_STR);
            $results->bindParam(':searchString2', $searchString, PDO::PARAM_STR);
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

function getMaxJournalEntryId() {
    include("inc/connection.php");
    try {
        $sql = "SELECT max(id)
                FROM entries";
        $results = $db->prepare($sql);
        $results->execute();        
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }

    $maxId = $results->fetchColumn(0);  

    return $maxId;
}

function getMinJournalEntryId() {
    include("inc/connection.php");
    try {
        $sql = "SELECT min(id)
                FROM entries";
        $results = $db->prepare($sql);
        $results->execute();        
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }

    $minId = $results->fetchColumn(0);  

    return $minId;
}

// Retrieve a single joournal entry to render on the detials page
function getJournalEntryById($id) {
    include("inc/connection.php");
    try {
        $sql = "SELECT *
                FROM entries
                WHERE id = :id";
        $results = $db->prepare($sql);
        $results->bindParam(':id', $id, PDO::PARAM_INT);
        $results->execute();        
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    
    return $results->fetch(PDO::FETCH_ASSOC);   // fetch is used in lieu of fetchAll becasue 
                                                // only one record should be returned, id is PRIMARY KEY
}

// Retrieve the journal entry resources to render on the details page
function getJournalEntryResources($id) {
    include("inc/connection.php");
    try {
        $sql = "SELECT name, link
                FROM resources
                JOIN entry_resources
                ON resources.id = entry_resources.resource_id
                WHERE entry_resources.entry_id = :id";
        $results = $db->prepare($sql);
        $results->bindParam(':id', $id, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchAll(PDO::FETCH_ASSOC);    // multiple records may be returned in the result set
                                                    // and we'll need an array to iterate over in the UI
}

// Check whether title is unique
function uniqueTitle($title) {
    include("inc/connection.php");
    $title = strtolower($title);    // ignore case
    try {
        $sql = "SELECT COUNT(id)
                FROM entries
                WHERE LOWER(title) = :title";
        $results = $db->prepare($sql);
        $results->bindParam(':title', $title, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    $count = $results->fetchColumn(0);
    if($count == 0) {   // title is unique
        return true;
    }
    else {  // title is not unique
        return false;
    }
}

// add a new entry to the journal (resources come in a separate function)
// by the time we reach this function, we know the title is unique
// returns true if no error
function addJournalEntry($title, $date, $timeSpent, $whatILearned) {
    include("inc/connection.php");
    try {
        $sql = "INSERT INTO entries (title, date, time_spent, learned)
                VALUES (:title, :date, :timeSpent, :whatILearned)";
        $results = $db->prepare($sql);
        $results->bindParam(':title', $title, PDO::PARAM_STR);
        $results->bindParam(':date', $date, PDO::PARAM_STR);
        $results->bindParam(':timeSpent', $timeSpent, PDO::PARAM_STR);
        $results->bindParam(':whatILearned', $whatILearned, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

// return an entry id from the database
// unique title entries are currently enforced in the UI
// this is really needed to add form resources to an existing entry
function getIdByTitle($title) {
    include("inc/connection.php");
    $title = strtolower($title);    // ignore case
    try {
        $sql = "SELECT id
                FROM entries
                WHERE LOWER(title) = :title";
        $results = $db->prepare($sql);
        $results->bindParam(':title', $title, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchColumn(0);
}

// Add a resource to the DB
function addResource($name, $link) {
    include("inc/connection.php");
    try {
        $sql = "INSERT INTO resources (name, link)
                VALUES (:name, :link)";
        $results = $db->prepare($sql);
        $results->bindParam(':name', $name, PDO::PARAM_STR);
        $results->bindParam(':link', $link, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

// Add resources for an existing entry (i.e. take from form post)
function addResourceToEntry($entryId, $resourceId) {
    include("inc/connection.php");
    try {
        $sql = "INSERT INTO entry_resources (entry_id, resource_id)
                VALUES (:entryId, :resourceId)";
        $results = $db->prepare($sql);
        $results->bindParam(':entryId', $entryId, PDO::PARAM_INT);
        $results->bindParam(':resourceId', $resourceId, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

// check whether a resource (by link) already exists
function resourceExists($link) {
    include("inc/connection.php");
    $link = strtolower($link);    // ignore case
    try {
        $sql = "SELECT count(id)
                FROM resources
                WHERE LOWER(link) = :link";
        $results = $db->prepare($sql);
        $results->bindParam(':link', $link, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    $count = $results->fetchColumn(0);
    if ($count != 0) {  // resource exists (or something went awry?)
        return true;
    }
    else {              // resource does not exist
        return false;
    }
}

// Get resource id by link
function getResourceIdByLink($link) {
    include("inc/connection.php");
    $link = strtolower($link);  // ignore case
    try {
        $sql = "SELECT id
                FROM resources
                WHERE LOWER(link) = :link";
        $results = $db->prepare($sql);
        $results->bindParam(':link', $link, PDO::PARAM_STR);
        $results->execute();
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchColumn(0);
}

// Get resource id by name
function getResourceIdByName($name) {
    include("inc/connection.php");
    $name = strtolower($name);  // ignore case
    try {
        $sql = "SELECT id
                FROM resources
                WHERE LOWER(name) = :name";
        $results = $db->prepare($sql);
        $results->bindParam(':name', $name, PDO::PARAM_STR);
        $results->execute();
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchColumn(0);
}

// Retrieve the journal entry tags to render in the UI
function getJournalEntryTags($id) {
    include("inc/connection.php");
    try {
        $sql = "SELECT name
                FROM tags
                JOIN entry_tags
                ON tags.id = entry_tags.tag_id
                WHERE entry_tags.entry_id = :id";
        $results = $db->prepare($sql);
        $results->bindParam(':id', $id, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchAll(PDO::FETCH_ASSOC);    // multiple records may be returned in the result set
                                                    // and we'll need an array to iterate over in the UI
}

// check whether tag already exists in the DB
function tagExists($tag) {
    include("inc/connection.php");
    $tag = strtolower($tag);    // ignore case
    try {
        $sql = "SELECT count(id)
                FROM tags
                WHERE LOWER(name) = :tag";
        $results = $db->prepare($sql);
        $results->bindParam(':tag', $tag, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }
    $count = $results->fetchColumn(0);
    if ($count != 0) {  // tag exists
        return true;
    }
    else {              // tag does not exist
        return false;
    }
}

// Add a tag to the DB
function addTag($tagName) {
    include("inc/connection.php");
    try {
        $sql = "INSERT INTO tags (name)
                VALUES (:tagName)";
        $results = $db->prepare($sql);
        $results->bindParam(':tagName', $tagName, PDO::PARAM_STR);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

// Get tag id by name
function getTagIdByName($tagName) {
    include("inc/connection.php");
    $name = strtolower($tagName);  // ignore case
    try {
        $sql = "SELECT id
                FROM tags
                WHERE LOWER(name) = :tagName";
        $results = $db->prepare($sql);
        $results->bindParam(':tagName', $name, PDO::PARAM_STR);
        $results->execute();
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchColumn(0);
}

// Get tag name by id
function getTag($tagId) {
    include("inc/connection.php");
    try {
        $sql = "SELECT name
                FROM tags
                WHERE id = :tagId";
        $results = $db->prepare($sql);
        $results->bindParam(':tagId', $tagId, PDO::PARAM_STR);
        $results->execute();
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
    return $results->fetchColumn(0);
}

// Add tags for an existing entry (i.e. take from form post)
function addTagToEntry($entryId, $tagId) {
    include("inc/connection.php");
    try {
        $sql = "INSERT INTO entry_tags (entry_id, tag_id)
                VALUES (:entryId, :tagId)";
        $results = $db->prepare($sql);
        $results->bindParam(':entryId', $entryId, PDO::PARAM_INT);
        $results->bindParam(':tagId', $tagId, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

function getMaxTagId() {
    include("inc/connection.php");
    try {
        $sql = "SELECT max(id)
                FROM tags";
        $results = $db->prepare($sql);
        $results->execute();        
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }

    $maxId = $results->fetchColumn(0);  

    return $maxId;
}

function getMinTagId() {
    include("inc/connection.php");
    try {
        $sql = "SELECT min(id)
                FROM tags";
        $results = $db->prepare($sql);
        $results->execute();        
    }
    catch(Exception $e) {
        echo $e->getMessage();
    }

    $minId = $results->fetchColumn(0);  

    return $minId;
}

// Pull entires from the database by tagId
// tagId is captured from the query string when getting the tag-search page
function getJournalEntriesByTagId($tagId, $limit = null, $offset = 0) {
    try {
        include("inc/connection.php");  
        $sql = "SELECT * FROM entries
                JOIN entry_tags ON entries.id = entry_tags.entry_id
                WHERE entry_tags.tag_id = :tagId";

        // If a limit is specified apped it to the SQL with the offset
        // if limit is an integer, we know one is passed in and its not null
        if (is_integer($limit)) {
            // append order by date
            $sql .= " ORDER BY `date` DESC";
            $sql .= " LIMIT :limit OFFSET :offset";
            $results = $db->prepare($sql);
            $results->bindParam(':tagId', $tagId, PDO::PARAM_INT);
            $results->bindParam(':limit', $limit, PDO::PARAM_INT);
            $results->bindParam(':offset', $offset, PDO::PARAM_INT);
        } else {  // No limit specified
            // append order by date
            $sql .= " ORDER BY `date` DESC";
            $results = $db->prepare($sql);
            $results->bindParam(':tagId', $tagId, PDO::PARAM_INT);
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

// Count journal entries by tag
function getJournalCountByTagId($tagId)
{
    try {
        include("inc/connection.php");
        $sql = "SELECT COUNT(id) FROM entries
                JOIN entry_tags ON entries.id = entry_tags.entry_id
                WHERE entry_tags.tag_id = :tagId";
        $results = $db->prepare($sql);
        $results->bindParam(':tagId', $tagId, PDO::PARAM_INT);
        $results->execute();
    } 
    catch (Exception $e) {
        echo $e->getMessage();
    }
        // return the count
        return $results->fetchColumn(0);
}

// Delete journal entry (DB on delete cascade will handle linking table entries)
function deleteJournalEntry($id) {
    include("inc/connection.php");
    try {
        $sql = "DELETE FROM entries
                WHERE id = :id";
        $results = $db->prepare($sql);
        $results->bindParam(':id', $id, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

function deleteJournalResources($id) {
    include("inc/connection.php");
    try {
        $sql = "DELETE FROM entry_resources
                WHERE entry_id = :id";
        $results = $db->prepare($sql);
        $results->bindParam(':id', $id, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

function deleteJournalTags($id) {
    include("inc/connection.php");
    try {
        $sql = "DELETE FROM entry_tags
                WHERE entry_id = :id";
        $results = $db->prepare($sql);
        $results->bindParam(':id', $id, PDO::PARAM_INT);
        $results->execute();
    }
    catch(Exception $e) {
        echo $e->getMessage() . "<br>";
        return false;
    }
    return true;
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//--VIEW FUNCTIONS--//
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function renderPaginationLinks($page, $currentPage, $totalPages, $tagId = null, $searchString = null)
{
    // Display links for pagination
    // If page number link matches current page disable the link
    // TODO: This will need to be modified if you ever add $searchString to tag-serach.php
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            echo "<li>" . $i . "</li>";
        } else {
            if (!empty($searchString)) {
                echo "<li><a href=\"" . $page . ".php?page=" . $i . "&searchQuery=" . $searchString . "\">" . $i . "</a></li>";
            } 
            elseif(!empty($tagId)) {
                echo "<li><a href=\"" . $page . ".php?page=" . $i . "&tagId=" . $tagId . "\">" . $i . "</a></li>";
            }
            else {
                echo "<li><a href=\"" . $page . ".php?page=" . $i . "\">" . $i . "</a></li>";
            }
        }
    }
}

// Function to check whether the id passed in is valid (i.e. within the range of entry ids)
// typically used for the id passed in the query string to a page to determine whether
// redirection is needed (i.e. if id is not valid, redirect...)
function validEntryIdChecker($id) {
    $minId = getMinJournalEntryId();
    $maxId = getMaxJournalEntryId();
    if (($id < $minId) || ($id > $maxId) || !(getJournalEntryById($id))) {  // if there's no entry by that id should return false
        $validId = false;
    }
    else {
        $validId = true;
    }
    return $validId;
}

// render tags in the UI
function renderTags($arr)
{

    echo "<div class=\"tag-block\">";
    echo "<ul class=\"tag-list\">\n";
    foreach ($arr as $tag) {
        $id = getTagIdByName($tag["name"]);
        echo "<li><a class=\"tag-link\" href=\"tag-search.php?tagId=" . $id . "\">#" . $tag["name"] . "</a></li>\n";
    }
    echo "</ul>\n";
    echo "</div>";
}
