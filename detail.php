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

// Check whether the get request leads to a valid entry
$validEntry = true;    // flag to indicate whether the current id is valid, assume it is to start
$minId = getMinJournalEntryId();
$maxId = getMaxJournalEntryId();
if (($id < $minId) || ($id > $maxId)) {
    $validEntry = false;
}

// Set the current journal entry to render equal to the one tied to the id passed in
if($validEntry) {
    $journalEntry = getJournalEntryById($id);
}
else {
    header("location:index.php");   // redirect home
}

?>

<section>
    <div class="container">
        <div class="entry-list single">
            <article>
                <h1><?php echo $journalEntry["title"]; ?></h1>
                <?php
                echo "<time datetime=\"" . $journalEntry["date"] . "\">" . date("F d, Y",strtotime($journalEntry["date"])) . "</time>\n";
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
                    <h3>Resources to Remember:</h3>
                    <ul>
                        <?php
                            $entryResources = getJournalEntryResources($id);
                            foreach($entryResources as $resource) {
                                echo "<li><a href=\"" . $resource["link"] . "\" target=\"_blank\">" . $resource["name"] . "</a></li>";
                            }
                        ?>
                        <!-- <li><a href="">Lorem ipsum dolor sit amet</a></li>
                        <li><a href="">Cras accumsan cursus ante, non dapibus tempor</a></li>
                        <li>Nunc ut rhoncus felis, vel tincidunt neque</li>
                        <li><a href="">Ipsum dolor sit amet</a></li> -->
                    </ul>
                </div>
            </article>
        </div>
    </div>
    <div class="edit">
        <p><a href="edit.php">Edit Entry</a></p>
    </div>
</section>

<?php
include("inc/footer.php");
?>