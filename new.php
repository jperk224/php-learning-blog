<?php

$pageTitle = "My Journal | New Entry";

include("inc/header.php");

// If this page is reached via GET, there will be nothing in the query string because
// there's no form to reach this page via GET method, only POST
// Therefore, we need POST logic; However, we first need variables to hold
// form values to populate the form in case there is an issue with the POST
// i.e. form field persistence!
// set as the default form values (i.e. <input value="$variable">)
// POSTing to the page will remember the values in the form if there are issues POSTing (e.g. user
// neglects to populate a required field)
// when the user is directed to this page in some way other than POST, setting these varianbles
// to empty strings will clear the form fields
// I hope these variable names a re self-explanatory :)

$title = "";
$date = "";
$timeSpent = "";
$whatILearned = "";
$resources = array();
$resourceIds = array();
$tags = "";
$tagArray = array();
$tagIds = array();

// Expand/reduce resources to add with one-line code change
// TODO: Make this adjustable via UI button for the user
$resourceInputCount = 3;

// POST logic -- assume successful POST!
// filter form input before adding to the DB for safety!
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $error_message = "";    // prep for errors...
    $title = trim(filter_input(INPUT_POST, "title", FILTER_SANITIZE_STRING));
    $timeSpent = trim(filter_input(INPUT_POST, "timeSpent", FILTER_SANITIZE_STRING));
    $whatILearned = trim(filter_input(INPUT_POST, "whatILearned", FILTER_SANITIZE_STRING));
    $date = trim(filter_input(INPUT_POST, "date", FILTER_SANITIZE_STRING));
    $tags = trim(filter_input(INPUT_POST, "tags", FILTER_SANITIZE_STRING));

    // turn the tags into an array of elements that can be stored in the DB
    // strip the # so as to store the tag w/o it
    if(!empty($tags)) {
        $tagsAsArray = explode(" ", $tags);
        foreach($tagsAsArray as $tag) {
            if($tag[0] == "#") {
                $tag = substr($tag, 1); // remove the #
            }
            else {
                $tag = $tag;
            }
            $tagArray[] = $tag;
        }
    }
    
    // check if the tag exists in the DB, if it does return the id, else add it, then grab
    // the id to add to the tagId array that will be added to the linking table
    if(count($tagArray) > 0) {
        foreach($tagArray as $tag) {
            if(tagExists($tag)) {
                $tagId = getTagIdByName($tag);
            }
            else {
                addTag($tag);
                $tagId = getTagIdByName($tag);
            }
            $tagIds[] = $tagId;
        }
    }

    // Create a nested array of resource name/link pairings to add to the DB
    for($i = 0; $i < $resourceInputCount; $i++) {
        $resource = "resource" . $i;
        $link = "resourceLink" . $i;
        $resourceName = trim(filter_input(INPUT_POST, $resource, FILTER_SANITIZE_STRING));
        $resourceLink = trim(filter_input(INPUT_POST, $link, FILTER_SANITIZE_STRING));  
        // TODO: This doesn't validate whether it's a valid link format ^^
        $resources[] = [$resourceName, $resourceLink];
    }

    // Resources array now holds resources from the POST, we need to check each one to see if it 
    // already exists in the DB.  If it does, return the current id to add to the linking table,
    // else, add it to the db, and get the resulting id to add to the linking table
    // from here, output an array of resource ids that will be added to the linking table for this post

    foreach($resources as $resource) {
        // convert the resource into its unique Id and add it to the Id array
        $id = -1;
        // names don't have to be unique, but links do, so we'll check for uniqueness if there's a link
        // there may be a name discrepency.  For now, keep the name currently in the DB
        // TODO: Edit name for an existing link?
        if(!empty($resource[1])) {
            if(resourceExists($resource[1])) {
                $id = getResourceIdByLink($resource[1]);
            }        
            else {
                // Add the resource to the DB first if it's not null, then get the id
                addResource($resource[0], $resource[1]);
                $id = getResourceIdByLink($resource[1]);
            }
        }
        else {  // use the name to add the resource to the DB and get the id
                // add the resouce to the DB by name
            if(!empty($resource[0])) {
                addResource($resource[0], $resource[1]);
                $id = getResourceIdByName($resource[0]);
            }
        }
        // add the id to the resource Id array if not -1
        if($id != -1) {
            $resourceIds[] = $id;
        }
    }

    // ensure the $date POSTed is valid, the date input box in the UI is not enough
    // date should be POSTed in YYYY-MM-DD format
    $dateMatch = explode("-", $date);   // convert the date value posted into an array deliminted by '-'
    //                                  // result SHOULD BE a 3 element array of yyyy mm dd

    // Validate date entry
    if((count($dateMatch) != 3)        // a valid date input should yield a 3 element array
                || (strlen($dateMatch[0]) != 4) // check for 4-digit year
                || (strlen($dateMatch[1]) != 2) // check for 2-digit month
                || (strlen($dateMatch[2]) != 2) // check for 2-digit day
                || (!checkdate($dateMatch[1], $dateMatch[2], $dateMatch[0]))) {   // check date is valid (month, day, year)
            $error_message = "Date entered is invalid.";
    }
    // title, time spent, what I learned are required
    elseif((empty($title)) || (empty($timeSpent)) || (empty($whatILearned))) {
        if((empty($title))) {
            $error_message = "Title is required.";
        }
        elseif((empty($timeSpent))) {
            $error_message = "Time spent is required.";
        }
        else {
            $error_message = "Didn't you learn something? It's required.";
        }
    }
    // NOTE: This is the only place unique journal title entries are currently enforced
    elseif(!uniqueTitle($title)) {
        $error_message = "Entry title already exists.  Title must be unique.";
    }
    else {
        // Add the journal entry to the DB
        if  (addJournalEntry($title, $date, $timeSpent, $whatILearned)) {  // returns true if journal entry added
            $addResource = true;
            // get the journal entry by title
            $entryId = getIdByTitle($title);

            // add resources to the existing entry if there are resources to add
            if(count($resourceIds) > 0) {
                foreach($resourceIds as $id) {
                    if (addResourceToEntry($entryId, $id)) {
                        continue;
                    }
                    else {
                        $error_message = "Error adding resources.  Please check <a href=\"detail.php?id=" . $entryId . "\">journal entry detail</a>.";
                        $addResource = false;
                        break;
                    }
                }
            }

            // add tags to the existing entry if there are tags to add
            if(count($tagIds) > 0) {
                foreach($tagIds as $id) {
                    if (addTagToEntry($entryId, $id)) {
                        continue;
                    }
                    else {
                        $error_message = "Error adding resources.  Please check <a href=\"detail.php?id=" . $entryId . "\">journal entry detail</a>.";
                        $addResource = false;
                        break;
                    }
                }
            }
            
            // if there was no error, redirect home
            if($addResource) {  
                header("location:index.php");
            }            
        }
        else {
            $error_message = "Error adding journal entry.";
        }
    }
}

?>

<section>
    <div class="container">
        <div class="new-entry">
            <h2>New Entry</h2>
            <?php
                // If we have an error render it!!
                if(!empty($error_message)) {
                    echo "<h3 style=\"color:red; text-align:center\">" . $error_message . "</h3><br>";
                }
            ?>
            <form method="post" action="new.php">
                <label for="title">Title</label>
                <input id="title" type="text" name="title"
                 value="<?php echo htmlspecialchars($title, ENT_NOQUOTES, 'UTF-8'); ?>"><br> <!-- TODO: ENT_NOQUOTES NOT working -->
                <label for="date">Date</label>
                <input id="date" type="date" name="date"
                value="<?php echo htmlspecialchars($date /*default escape , default encoding (UTF-8)*/); ?>"><br>
                <label for="time-spent"> Time Spent</label>
                <input id="time-spent" type="text" name="timeSpent"
                value="<?php echo htmlspecialchars($timeSpent /*default escape , default encoding (UTF-8)*/); ?>"><br>
                <label for="what-i-learned">What I Learned</label>
                <textarea id="what-i-learned" rows="5" name="whatILearned"><?php echo htmlspecialchars($whatILearned /*default escape , default encoding (UTF-8)*/); ?></textarea>
                <fieldset>
                    <legend>Resources To Remember</legend>
                    <?php 
                    for($i = 0; $i < $resourceInputCount; $i ++) {
                    // value attribute for form persistence
                    if(count($resources) > 0) {
                        $resourceName = $resources[$i][0];
                        $resourceLink = $resources[$i][1];    
                    }
                    
                    echo "<div id=\"resource-info\">\n";
                    echo "<div class=\"resource-name\">\n";
                    echo "<label for=\"resource" . $i . "\">Name</label>\n";
                    echo "<input type=\"text\" id=\"resource" . $i . "\" name=\"resource" . $i . "\"";
                    if(isset($resourceName)) {
                        echo "\" value=\"" . $resourceName . "\">\n";  
                    } 
                    else {
                        echo ">\n"; 
                    }
                    echo "</div>\n";
                    echo "<div class=\"resource-link\">\n";
                    echo "<label for=\"resource-link" . $i . "\">Link</label>\n";
                    echo "<input type=\"text\" id=\"resource-link" . $i . "\" name=\"resourceLink" . $i . "\""; 
                    if(isset($resourceLink)) {
                        echo "\" value=\"" . $resourceLink . "\">\n";  
                    } 
                    else {
                        echo ">\n"; 
                    }
                    echo "</div>\n";
                    echo "</div>";
                    }
                    ?>
                </fieldset>
                <br>
                <label for="tag-input">Tags</label>
                <textarea id="tags" rows="2" name="tags"><?php echo htmlspecialchars($tags); ?></textarea>
                <input type="submit" value="Publish Entry" class="button">
                <a href="index.php" class="button button-secondary">Cancel</a>
            </form>
        </div>
    </div>
</section>

<?php
include("inc/footer.php");
?>