<?php

$pageTitle = "My Journal | Home";

include("inc/header.php");

?>

        <section>
            <div class="container">
                <div class="entry-list">
                    <?php
                        $journalEntries = getJournalEntries();
                        foreach($journalEntries as $item) {
                            echo "<article>\n";
                            echo "<h2><a href=\"#\">" . $item["title"] . "</a></h2>\n";
                            echo "<time datetime=\"" . $item["date"] . "\">" . $item["date"] . "</time>\n";
                            echo "</article>\n";
                        }
                    ?>
                </div>
            </div>
        </section>

    <?php
    include("inc/footer.php");
    ?>