<?php
/*
 * Specifičtější pohled na článek (rubriky a komentáře).
 * */
    $articles = new ArticleDatabase();
    $value = $articles->getArticle($_GET["p"]);
    $categories = $articles->getArticleCategories($_GET["p"]);
    $location = $_SERVER['PHP_SELF']."?p=edit";
    $commDb = new CommentsDatabase();
    $comments = $commDb->getAllArticleComments($_GET["p"]);
    if ($articles->articleExists($_GET["p"])) {
?>
<article id="article">
    <h2 id="title"><?php echo htmlspecialchars($value["title"], ENT_QUOTES) ?></h2>
    <?php // správa článků v uživatelském rozhraní - viditelná pouze pro admina
    if (isset($_SESSION["admin"])&&$_SESSION["admin"]) { ?>
        <form action="<?php echo $location; ?>" method="post">
            <input type="hidden" name="a" value="<?php if (isset($_GET["p"])) {echo $_GET["p"];} ?>">
            <button type="submit" name="delete">Smazat</button>
            <button type="submit" name="edit">Upravit</button>
        </form>
        <br>
    <?php } ?>
    <div id="info">
        Zveřejněno dne <span id="date"><?php echo $value["timestamp"]; ?></span> uživatelem <span>
            <a href="<?php echo $_SERVER['PHP_SELF']."?author=".urlencode(htmlspecialchars($value["author"], ENT_QUOTES));?>"><?php echo htmlspecialchars($value["author"], ENT_QUOTES); ?></a></span>
    </div>
    <div id="text">
        <p><?php echo nl2br(htmlspecialchars($value["contents"]), ENT_QUOTES)?> </p>
    </div>

    <?php
        foreach ($categories as $category) { ?>
            <div class='category'><a href="<?php echo $_SERVER['PHP_SELF']."?cat=".urlencode($category["category"])?>"><?php echo htmlspecialchars($category["category"])?></a></div>
        <?php } ?>
    <hr>
    <h3>Komentáře</h3>
    <?php
        if (sizeof($comments) != 0) { foreach ($comments as $id => $value) { ?>
            <div class="comment">
                <p><span class="comment_author"><?php echo htmlspecialchars($value["author"], ENT_QUOTES); ?></span>
                    (<?php echo $value["timestamp"]; ?>) k&nbsp;tomu řekl: </p>
                <p> <?php echo nl2br(htmlspecialchars($value["text"], ENT_QUOTES)) ?> </p>
                            <?php if (isset($_SESSION["user"])&&($_SESSION["user"] == $value["author"]||$_SESSION["admin"])) {?>
                                <form method="post" action="<?php echo $_SERVER['PHP_SELF']."?p=".urlencode($_GET["p"]); ?>">
                                    <input type="hidden" name="comment" value="<?php echo $value["id"]; ?>">
                                    <button type="submit" name="deleteComment">Smazat</button>
                                </form>
                            <?php } ?>
                <hr>
            </div>
            <?php
        }
        }
        else {
            if (isset($_SESSION["signed"]) && $_SESSION["signed"]) { ?>
                <hr>
                <div class="comment">
                    <p>
                        Buďte první, kdo toto okomentuje.
                    </p>
                </div>
            <?php }
        }
        if (!isset($_SESSION["signed"])) {
    ?>
    <div class="comment">
        <p>
            Přihlašte se, abyste mohli komentovat.
        </p>
    </div>
    <?php  }
    if (isset($_SESSION["signed"])&&$_SESSION["signed"]) {?>
    <form action="<?php echo $_SERVER['PHP_SELF']."?p=".$_GET["p"]; ?>" method="post" id="new_comment">
        <label>Přidat nový komentář:<br>
            <textarea name="comment_content" required ><?php if (isset($_POST["comment_content"])) {echo $_POST["comment_content"];}?></textarea>
        </label>
        <br>
        <button type="submit" name="submit_comment">Přidat</button>
    </form>
<?php }
    if (isset($_POST["comment_content"])&&!commentValid($_POST["comment_content"])&&strlen($_POST["comment_content"]) == 0) { ?>
        <div class="error_message">Komentář je prázdný. </div>
    <?php }
    else if (isset($_POST["comment_content"])&&!commentValid($_POST["comment_content"])&&strlen($_POST["comment_content"]) > 900) {?>
        <div class="error_message">Komentář je příliš dlouhý.</div>
    <?php } ?>
</article>
<?php }
else {
?>
<h2>Stránka, kterou hledáte, neexistuje</h2>
    <p>Je nám líto, ale hledáte stránku, která neexistuje. Buďto byla smazána, nebo nikdy neexistovala. </p>
<?php } ?>
