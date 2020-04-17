<?php

$pageTitle = "My Journal | #tagSearch";
$itemsPerPage = 5;  // fixed count for display pagination

include("inc/header.php");

//GET VARIABLES//
//////////////////////////////////////////////

// You should only reach this by a GET request with a tagId param
if(isset($_GET["tagId"])) {
    $tagId = filter_input(INPUT_GET, "tagId", FILTER_SANITIZE_NUMBER_INT);
}

$tag = getTag($tagId);

// redirect home if the tagId provided is out of the range of tags or tag doesn't exist
$maxTagId = getMaxTagId();
$minTagId = getMinTagId();
if((empty($tag)) || ($tagId < $minTagId) || ($tagId > $maxTagId)) {
    header("location:index.php");
}

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

$totalJournalEntries = getJournalCountByTagId($tagId);

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
                        // TODO: ADD Pagination arrows and limit count exposed for scpace constraints
                        renderPaginationLinks("tag-search", $currentPage, $totalPages, $tagId);
                        ?>
                    </ul>
                </div> <!-- end pagination-links -->
            </div> <!-- end entry-header -->
            <?php

            $journalEntries = getJournalEntriesByTagId($tagId, $itemsPerPage, $offset);
            
            // if there are results to render total journal entries will be greater than 0
            // else we need to render no results
            if($totalJournalEntries > 0) {
                foreach ($journalEntries as $item) {
                    echo "<article>\n";
                    echo "<h2><a href=\"detail.php?id=" . $item["id"] . "\">" . $item["title"] . "</a></h2>\n";
                    // use data() and strtotime() to render fancy long date
                    echo "<time datetime=\"" . $item["date"] . "\">" . date("F d, Y",strtotime($item["date"])) . "</time>\n";
                    // find all tags associated with each journal entry item and render it
                    $tags = getJournalEntryTags($item["id"]);
                    if(count($tags) > 0) {
                        renderTags($tags);
                    }
                    echo "</article>\n";
                }
            }
            else {
                echo "<article>\n";
                echo "<h2>No Entries Found.";
                echo "<p><a href=\"index.php\">Home</a></p>";
            }
            ?>
        </div>
    </div>
</section>

<?php
include("inc/footer.php");
?>
