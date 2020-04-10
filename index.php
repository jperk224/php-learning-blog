<?php

$pageTitle = "My Journal | Home";
$itemsPerPage = 5;  // fixed count for display pagination
$searchQuery = '';  // to drive search results, if set results returned will
                    // match SQL 'like'

include("inc/header.php");

//GET VARIABLES//
//////////////////////////////////////////////
// GET variable identifying the pagination page for SQL offsets
// The variable is taken from the query string, so filter it-- safety first!
if (isset($_GET["page"])) {
    $currentPage = filter_input(INPUT_GET, "page", FILTER_SANITIZE_NUMBER_INT);
}

// GET variable is search is used
if (isset($_GET["searchQuery"])) {
    $searchQuery = filter_input(INPUT_GET, "searchQuery", FILTER_SANITIZE_STRING);  // remove potential HTML tags
}

// If there is no page variable in the query string, current page is empty, 
// set the current page to 1
if (empty($currentPage)) {
    $currentPage = 1;
}

// this count is needed to determine number of pagination pages
if(empty($searchQuery)) {
    $totalJournalEntries = getJournalEntryCount();
}
else {
    $totalJournalEntries = getJournalEntryCount($searchQuery);
}

// determine total pages, round up to ensure there's enough pages to paginate over
// total journal entries is 0 if the search query returns no results, so
// set total pages to 1
if($totalJournalEntries > 0) {
    $totalPages = ceil($totalJournalEntries / $itemsPerPage);
}
else {
    $totalPages = 1;
}

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
                        // FIXME: ADD Pagination arrows
                        if(!empty($searchQuery)) {
                        renderPaginationLinks($currentPage, $totalPages, $searchQuery);
                        }
                        else {
                        renderPaginationLinks($currentPage, $totalPages);
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
            // If search query, show only entries returned from search query
            if(!empty($searchQuery)) {
                // Grab all entries matching search query
                $journalEntries = getJournalEntries($itemsPerPage, $offset, $searchQuery);
            }
            else {
                // If no search query, show all entries
                $journalEntries = getJournalEntries($itemsPerPage, $offset);
            }

            // if there are results to render total journal entries will be greater than 0
            // else we need to render no results
            if($totalJournalEntries > 0) {
                foreach ($journalEntries as $item) {
                    echo "<article>\n";
                    echo "<h2><a href=\"detail.php?id=" . $item["id"] . "\">" . $item["title"] . "</a></h2>\n";
                    // use data() and strtotime() to render fancy long date
                    echo "<time datetime=\"" . $item["date"] . "\">" . date("F d, Y",strtotime($item["date"])) . "</time>\n";
                    echo "</article>\n";
                }
            }
            else {
                echo "<article>\n";
                echo "<h2>No Entries Found Matching \"" . $searchQuery . "\".";
                echo "<p><a href=\"index.php\">Home</a></p>";
            }
                
            ?>
        </div>
    </div>
</section>

<?php
include("inc/footer.php");
?>
