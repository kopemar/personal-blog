<?php
include "switch.php";

/**
 * Hlavní vzhledová část (společná pro všechny stránky).
 * */
$articles= new ArticleDatabase();
$users = new UserDatabase();
$theme = "theme_light";

?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <link type="text/css" rel="stylesheet" href="theme.css">
    <?php if ((isset($_COOKIE['themes']) && ($_COOKIE['themes'] == "theme_dark"))) { ?>
    <link type="text/css" rel="stylesheet" href="theme_dark.css">
    <?php } else { ?>
    <link type="text/css" rel="stylesheet" href="theme_light.css">
    <?php }?>
    <title><?php echo getTitle() ?></title>
</head>
<body>
<div id="page_content">
    <header class="js_design" id="header">
        <h1><a href="<?php echo $_SERVER['PHP_SELF']?>">Lorem ipsum</a></h1>
        <div id="head_menu">
            <div class="head_menu_item"><a href="?p=about"> <img src="drawable/<?php if (isset($_COOKIE["themes"]) && ($_COOKIE['themes'] == "theme_dark")) {echo "white/";} ?>about.png" alt="head_menu_link_about"></a></div>
            <div class="head_menu_item"><a href="?p=gallery"><img src="drawable/<?php if (isset($_COOKIE["themes"]) && ($_COOKIE['themes'] == "theme_dark")) {echo "white/";} ?>gallery.png" alt="head_menu_link_gallery"> </a></div>
            <div class="head_menu_item"><a href="?p=info"><img src="drawable/<?php if (isset($_COOKIE["themes"]) && ($_COOKIE['themes'] == "theme_dark")) {echo "white/";} ?>info.png" alt="head_menu_link_faq"></a></div>
        </div>
    </header>

    <div id="main_page">
        <main id="article_content">
        <?php getContents();?>
        </main>
        <img alt="back_to_top_button" id="back_to_top" class="js_design back_to_top" src="drawable/top.png">

<aside id="main_menu">
    <div class="menu_category">
        <div class="menu_label">Uživatelský profil</div>
        <?php if (isset($_SESSION["user"])) {
            echo "Přihlášený uživatel ".htmlspecialchars($_SESSION["user"], ENT_QUOTES);
        }
        else echo "Nejste přihlášeni";
        ?>
        <?php if (isset($_SESSION["signed"])&&$_SESSION["signed"]) { ?>
            <br>
        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <button type="submit" name="signout">Odhlásit</button>
        </form>
        <?php } ?>
    </div>

    <div class="menu_category">
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" id="theme_opt">
            <label> <span class="menu_label">Nastavení vzhledu</span>
            <select name="themes">
                <option value="theme_light" <?php if (!(isset($_COOKIE['themes']))) { ?> selected <?php } ?> >Světlé</option>
                <option value="theme_dark" <?php if (isset($_COOKIE['themes'])) { ?>selected <?php } ?> >Tmavé</option>
            </select>
            </label>
            <button type="submit" name="theme_submit" value="theme_submit">Změnit</button>
        </form>
    </div>

    <div class="menu_category">
        <div class="menu_label">Rubriky</div>
        <ul>
            <?php foreach ($articles->getAllCategories() as $category) {?>
                <li><a href="<?php echo $_SERVER['PHP_SELF']."?cat=".urlencode($category["category"]) ?>"><?php echo htmlspecialchars($category["category"])?></a></li>
            <?php } ?>
        </ul>
    </div>
    <?php if (isset($_SESSION["admin"])&&$_SESSION["admin"] == true) { ?>
    <div class="menu_category">
        <div class="menu_label">Administrace</div>
        <ul>
            <li><a href="index.php?p=edit">Přidání nového článku</a> </li>
        </ul>
    </div>
    <?php } ?>
    <?php if (!isset($_SESSION["signed"])) { ?>
    <div class="menu_category">
        <div class="menu_label">Přihlášení</div>
        <form id="login" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" novalidate>
            <label>
                Uživatelské jméno<br>
                <input type="text" name="username" placeholder="Uživatelské jméno" value="<?php if (isset($_POST["username"])) {echo $_POST["username"];}?>" id="user" required>
            </label>
            <br>
            <label>
                Heslo<br>
                <input type="password" name="password" placeholder="Heslo" id="pass" required>
            </label>
            <br>
            <input type="submit" name="login" id="login_btn" value="Log in">
        </form>
        <?php if (isset($_POST["username"])&&isset($_POST["password"])) {if (!$users->userExists($_POST["username"])||!password_verify($_POST["password"], $users->getPasswordHash($_POST["username"]))) { ?>
            <div class="error_message">Zadané uživatelské jméno neexistuje nebo jste zadali špatné heslo.</div>
        <?php } }?>
        <a href="index.php?p=register">Zaregistrovat se</a>
    </div>
    <?php } ?>
</aside>
    </div>

<footer>
    <p>&copy;&nbsp;Autor, <?php echo $year = date("Y");?></p>
</footer>
</div> <!-- closing tag for #page_content -->
<script src="theme.js"></script>
<script src="login.js"></script>
</body>
</html>
