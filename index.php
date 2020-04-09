<?php

$pageTitle = "My Journal | Home";
$itemsPerPage = 5;  // fixed count for display pagination

include("inc/header.php");

// GET variable identifying the pagination page for SQL offsets
// The variable is taken from the query string, so filter it-- safety first!
if (isset($_GET["page"])) {
    $currentPage = filter_input(INPUT_GET, "page", FILTER_SANITIZE_NUMBER_INT);
}

// If there is no page variable in the query string, current page is empty, 
// set the current page to 1
if (empty($currentPage)) {
    $currentPage = 1;
}

// this count is needed to determine number of pagination pages
$totalJournalEntries = getJournalEntryCount();

// determine total pages, round up to ensure there's enough pages to paginate over
$totalPages = ceil($totalJournalEntries / $itemsPerPage);

// determine the offset for pagination
// e.g. with 5 entries per page, the offset on page 2 is 5 so page 2 displays entries 6 - 10
$offset = ($currentPage - 1) * $itemsPerPage;

// Redirect the user to the last page of entries if page in the query string exceeds total pages
if ($currentPage > $totalPages) {
    header("location:index.php?page=" . $totalPages);
}

// Redirect the user to the first page of entries if page in the query string is less than 1
if ($currentPage < 1) {
    header("location:index.php?page=1");
}

?>

<section>
    <div class="container">
        <div class="entry-list">
            <div class="entry-header">
                <div class="pagination-links">
                    <p>Page: </p>
                    <ul>
                        <?php
                        // Display links for pagination
                        // If page number link matches current page disable the link
                        for ($i = 1; $i <= $totalPages; $i++) {
                            if ($i == $currentPage) {
                                echo "<li>" . $i . "</li>";
                            } else {
                                echo "<li><a href=\"index.php?page=" . $i . "\">" . $i . "</a></li>";
                            }
                        }
                        ?>
                    </ul>
                </div> <!-- end pagination-links -->
                <div class="search-form">
                    <form action="index.php" method="get">
                        <label for="search-box">Search: </label>
                        <input type="text" id="search-box" name="searchQuery">
                        <button class="button"><i class="fa fa-search" aria-hidden="true"></i></button>
                    </form>
                </div> <!-- end search-form -->
            </div> <!-- end entry-header -->
            <?php
            $journalEntries = getJournalEntries($itemsPerPage, $offset);
            foreach ($journalEntries as $item) {
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