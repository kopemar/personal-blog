<?php

$articles = new ArticleDatabase();

/**
 * Pro zobrazení výpisů článků (podle kritérií či bez nich).
 * */

if (isset($_GET["author"])&&isset($_GET["cat"])) {
    $array = $articles->getArticlesByCategoryAuthor($_GET["cat"], $_GET["author"]);
}
else if (isset($_GET["cat"])) {
    $array = $articles->getArticlesByCategory($_GET["cat"]);
}
else if (isset($_GET["author"])) {
    $array = $articles->getArticlesByAuthor($_GET["author"]);
}
else {
    $array = $articles->getAllArticles();
}

/**
 * počet článků na stránku
 */

if (isset($_COOKIE["perpage"])) {
    $perPage = intval($_COOKIE["perpage"]);
}
else {
    $perPage = 3;
}

/**
 * výpočet pro stanovení počtu stránek
 */
$maxIndex = ceil(sizeof($array) / $perPage);

/**
 * stanovení akuálního čísla stránky
 */
if(isset($_GET["pages"])){
    if (intval($_GET["pages"]) <= $maxIndex) {
        $page = intval($_GET["pages"]);
    }
    else {
        $page = $maxIndex;
    }
    if (intval($_GET["pages"]) >= 1) {
        $page = intval($_GET["pages"]);
    }
    else {
        $page = 1;
    }
}
else {
    $page = 1;
}

/**
 * pro stránkování -- rozdělení pole na menší části
*/
$a = array_slice(array_reverse($array, true), ($page-1) * $perPage, $perPage, true);
/**
 * výpis článků podle šablony
 */
foreach ($a as $value) {

?>
<section>
    <h2><a href="index.php?p=<?php echo urlencode($value["id"]) ?>"><?php echo htmlspecialchars($value["title"], ENT_QUOTES); ?></a></h2>
    <div class="info">
        Zveřejněno dne <span><?php echo $value["timestamp"]; ?></span> uživatelem <span><a href="<?php echo $_SERVER['PHP_SELF']."?author=".urlencode(htmlspecialchars($value["author"], ENT_QUOTES));?>">
                <?php echo htmlspecialchars($value["author"], ENT_QUOTES); ?></a></span>
    </div>
    <p> <?php echo nl2br(htmlspecialchars($value["contents"]))?> </p>
    <hr>
</section>


<?php
}
?>
<div id="pages">
    <div id="older"><?php if ($page < $maxIndex) { ?><a href="<?php echo getLocation(); echo "&pages=".($page+1);?>">Starší</a>
        <?php } ?></div>
    <div id="page_number">Strana <?php echo htmlspecialchars($page, ENT_QUOTES) ?> z <?php echo $maxIndex ?></div>
    <div id="newer"><?php if ($page > 1) { ?><a href="<?php echo getLocation(); echo "&pages=".($page-1);?>">Novější</a>
        <?php } ?></div>
</div>
    <form method="post" action="<?php echo getLocation(); ?>" id="perpage">
        <label>Počet článků na stránce:
        <select name="perpage">
            <option value="3" <?php if (isset($_COOKIE["perpage"])&&intval($_COOKIE["perpage"])==3 ) echo "selected"?>>3</option>
            <option value="5" <?php if (isset($_COOKIE["perpage"])&&intval($_COOKIE["perpage"])==5 ) echo "selected"?>>5</option>
            <option value="10" <?php if (isset($_COOKIE["perpage"])&&intval($_COOKIE["perpage"])==10 ) echo "selected"?>>10</option>
        </select>
        </label>
        <button type="submit">Change</button>
    </form>
