<?php
$DB = new ArticleDatabase();
if (isset($_POST["a"])) {
    $article = $DB->getArticle($_POST["a"]);
    $allCat = $DB->getArticleCategories($_POST["a"]);
    $categories = array();
    foreach ($allCat as $value) {
        array_push($categories, $value["category"]);
    }
}
// show it only if user is signed in as an admin
if (isset($_SESSION["admin"])&&$_SESSION["admin"]) { ?>
<h2>Přidat nový článek</h2>
<form id="edit" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?p=edit" novalidate>
    <?php if (isset($_POST["a"])) { ?>
        <input type="hidden" name="a" value="<?php echo $_POST["a"]; ?>">
    <?php } ?>
    <label> Nadpis:
        <input type="text" class="wide" name="edit_article_title" id="edit_article_title" placeholder="Nadpis" value="<?php // if the user is editing, show him the contents of the article
        if (isset($_POST["edit_article_title"])) {echo htmlspecialchars($_POST["edit_article_title"]);}
        else if (isset($article)) {echo htmlspecialchars($article["title"]);} ?>" required >
    </label>
    <br>
    <p>Autor: <?php //name of author cannot be changed in UI - who added it is now held in db
         if (isset($article)) {echo $article["author"];} else if (isset($_SESSION["user"])) echo $_SESSION["user"]?></p>
    <p>Rubriky:
        <input type="hidden" name="category[]" value="">
        <?php foreach ($DB->getAllCategories() as $category) {?>
            <label><?php echo htmlspecialchars($category["category"], ENT_QUOTES); ?> <input type="checkbox" name="category[]" value="<?php echo htmlspecialchars($category["category"], ENT_QUOTES)?>"
                <?php if (isset($categories)&&in_array($category["category"], $categories)) echo "checked"?>></label>
        <?php } ?>
        <span id="categories_fields">
            <label>Jiné:
        <input type="text" name="category[]" class="new_category_field" placeholder="Jiná kategorie">
                </label>
        </span>
        <button type="button" id="another_category">Přidat pole</button>
    </p>
    <label>Text<br>
        <textarea id="edit_article_text" placeholder="Text článku" name="edit_article_text" required><?php if (isset($_POST["edit_article_text"])) {echo htmlspecialchars($_POST["edit_article_text"]);}
            else if (isset($article)) {echo htmlspecialchars($article["contents"]);} ?></textarea>
    </label>
    <br>
    <input type="submit" name="submit_article" value="Submit">
</form>
    <?php if (isset($_POST["submit_article"])) {if (!titleValid($_POST["edit_article_title"])&&strlen($_POST["edit_article_title"])==0) {?>
        <div class="error_message">Vyplňte titulek</div>
    <?php } else if (!titleValid($_POST["edit_article_title"])&&strlen($_POST["edit_article_title"])> 250) {
        ?>
        <div class="error_message">Titulek je moc dlouhý!</div>
        <?php }
    if (!textValid($_POST["edit_article_text"])&&strlen($_POST["edit_article_text"]) == 0) { ?>
        <div class="error_message">Článek by měl mít obsah.</div>
<?php }
    else if (!textValid($_POST["edit_article_text"])&&strlen($_POST["edit_article_text"]) > 8000) {
    ?>
        <div class="error_message">Článek je moc dlouhý.</div>
    <?php }
}}
else { ?>
<h2>Bez oprávnění</h2>
<p>Nemáte oprávnění administrátora.</p>
<?php } ?>

<script src="edit.js"></script>
