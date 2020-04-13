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

    // Create a nested array of resource name/link pairings to add to the DB
    for($i = 0; $i < $resourceInputCount; $i++) {
        $resource = "resource" . $i;
        $link = "resourceLink" . $i;
        $resourceName = trim(filter_input(INPUT_POST, $resource, FILTER_SANITIZE_STRING));
        $resourceLink = trim(filter_input(INPUT_POST, $link, FILTER_SANITIZE_STRING));  
        // TODO: This doesn't validate whether it's a valid link format ^^
        $resources[] = [$resourceName, $resourceLink];
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
            header("location:index.php");
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
                <!-- <label for="resources-to-remember">Resources to Remember</label> -->
                <!-- <textarea id="resources-to-remember" rows="5" name="resourcesToRemember"><?php echo htmlspecialchars($resources /*default escape , default encoding (UTF-8)*/); ?></textarea>
                <input type="submit" value="Publish Entry" class="button"> -->
                <fieldset>
                    <legend>Resources To Remember</legend>
                    <?php 
                    for($i = 0; $i < $resourceInputCount; $i ++) {
                    echo "<div id=\"resource-info\">\n";
                    echo "<div class=\"resource-name\">\n";
                    echo "<label for=\"resource" . $i . "\">Name: </label>\n";
                    echo "<input type=\"text\" id=\"resource" . $i . "\" name=\"resource" . $i . "\">\n";
                    echo "</div>\n";
                    echo "<div class=\"resource-link\">\n";
                    echo "<label for=\"resource-link" . $i . "\">Link: </label>\n";
                    echo "<input type=\"text\" id=\"resource-link" . $i . "\" name=\"resourceLink" . $i . "\">\n";
                    echo "</div>\n";
                    echo "</div>";
                    }
                    ?>
                </fieldset>
                <br>
                <input type="submit" value="Publish Entry" class="button">
                <a href="index.php" class="button button-secondary">Cancel</a>
            </form>
        </div>
    </div>
</section>

<?php
include("inc/footer.php");
?>