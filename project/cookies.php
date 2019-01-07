<?php

/*
 * Část kódu na nastavení cookies (které musí být odeslány na začátku).
 * */

/**
 * @return string
 */
function getLocation () {
    if (isset($_GET["p"])) {
        $location = $_SERVER['PHP_SELF']."?p=".$_GET["p"];
    }
    else if (isset($_GET["cat"])&&isset($_GET["author"])) {
        $location = $_SERVER["PHP_SELF"]."?cat=".urlencode($_GET["cat"])."&author=".urlencode($_GET["author"]);
    }
    else if (isset($_GET["cat"])) {
        $location = $_SERVER["PHP_SELF"]."?cat=".urlencode($_GET["cat"]);
    }
    else if (isset($_GET["author"])) {
        $location = $_SERVER["PHP_SELF"]."?author=".urlencode($_GET["author"]);
    }
    else {
        $location = $_SERVER["PHP_SELF"]."?";
    }
    return $location;
}

if (isset($_POST["theme_submit"])) {
    if ($_POST['themes'] == "theme_dark") {
        setcookie("themes", "theme_dark", time() + 2400);
        $theme = "theme_dark";
    } else if ($_POST['themes'] == "theme_light") {
        setcookie("themes", null);
        $theme = "theme_light";
    }

    $location = getLocation();

    header("Location: $location");
}


// articles per page count (user-settings based or default value)
if (isset($_POST["perpage"])) {
    $perPage = intval($_POST["perpage"]);
    if (!isset($_COOKIE["perpage"])) {
        setcookie("perpage", $perPage);
    }
    else {
        setcookie("perpage", $perPage, time() + 2400);
    }
    header("Location: ".getLocation());
}
else if (isset($_COOKIE["perpage"])) {
    $perPage = intval($_COOKIE["perpage"]);
}
else {
    $perPage = 3;
}