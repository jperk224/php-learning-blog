<?php

$pageTitle = "My Journal | Entry Detail";

include("inc/header.php");

//GET VARIABLES//
//////////////////////////////////////////////
// If you reach this page via a GET, it's coming from the link 
// embedded in the entry title which passes the item id from the DB
if (isset($_GET["id"])) {
    $id = filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
} else {
    $id = 0;    // Set to zero to trigger NOT FOUND if coming from some other way
}

// Check whether the id passed in is valid
$validEntry = validEntryIdChecker($id);

// Set the current journal entry to render equal to the one tied to the id passed in
if ($validEntry) {
    $journalEntry = getJournalEntryById($id);
} else {  // redirect home if the id is invalid
    header("location:index.php");
}

?>

<section>
    <div class="container">
        <div class="entry-list single">
            <article>
                <h1><?php echo $journalEntry["title"]; ?></h1>
                <?php
                echo "<time datetime=\"" . $journalEntry["date"] . "\">" . date("F d, Y", strtotime($journalEntry["date"])) . "</time>\n";
                $tags = getJournalEntryTags($journalEntry["id"]);
                if (count($tags) > 0) {
                    renderTags($tags);
                }
                ?>
                <div class="entry">
                    <h3>Time Spent: </h3>
                    <p><?php echo $journalEntry["time_spent"]; ?></p>
                </div>
                <div class="entry">
                    <h3>What I Learned:</h3>
                    <p><?php echo $journalEntry["learned"]; ?></p>
                </div>
                <div class="entry">
                    <?php
                    $entryResources = getJournalEntryResources($id);
                    if (sizeof($entryResources) > 0) {
                        echo "<h3>Resources to Remember:</h3>";
                        echo "<ul>";
                        foreach ($entryResources as $resource) {
                            if (!empty($resource["link"])) {
                                echo "<li><a href=\"" . $resource["link"] . "\" target=\"_blank\">" . $resource["name"] . "</a></li>";
                            } else {
                                echo "<li>" . $resource["name"] . "</li>";
                            }
                        }
                        echo "</ul>";
                    }
                    ?>
                </div>
            </article>
        </div>
    </div>
    <div class="edit">
        <!-- TODO: Send the id to the edit page so form is pre-populated with existing info -->
        <!-- <p><a href="edit.php">Edit Entry</a></p> -->
        <div>
            <ul class="entry-links">
            <?php   
            echo "<li><a href=\"edit.php?id=" . $id . "\">Edit Entry</a></li>\n";
            echo "<li><a href=\"#\">Delete Entry</a></li>\n";
            // echo "<form action=\"edit.php?id=" . $id . "\" method=\"get\">\n";
            // echo "<button class=\"button\">Edit Entry</button>\n";
            // echo "</form>\n";
            // echo "<br>\n";            
            // echo "<form action=\"#\" method=\"post\">\n";
            // echo "<button class=\"button\">Delete Entry</button>\n";
            // echo "</form>";
        ?>
            </ul>
        
        </div> <!-- end entry-buttons class -->
    </div>
</section>

<?php
include("inc/footer.php");
?>
