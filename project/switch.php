<?php

/**
 * @return mixed -- includne, co je podle dotazu potřeba (statické stránky nebo dynamicky generovaný article)
 */
function getContents()
{
    if (isset($_GET["p"])) {
        if ($_GET["p"] === "register") {
            return include "register.php";
        } else if ($_GET["p"] === "edit") {
            return include "edit.php";
        } else if ($_GET["p"] == "info") {
            return include "info.php";
        } else if ($_GET["p"] == "gallery") {
            return include "gallery.php";
        } else if ($_GET["p"] == "about") {
            return include "about.php";
        }
        else {
            $articles = new ArticleDatabase();
            $articles->getArticle($_GET["p"]);
            return include "article.php";
        }
    }
    else {
        return include "homepage.php";
    }
}

/**
 * @return string -- vhodný titulek pro článek
 */
function getTitle() {
    $title = "Title";
    if (isset($_GET["p"])) {
        if ($_GET["p"] === "register") {
            $title = "Registrace";
        }
        else if ($_GET["p"] === "edit") {
            $title = "Editace článku";
        }
        else if ($_GET["p"] == "about") {
            $title = "O mně";
        }
        else if ($_GET["p"] == "gallery") {
            $title = "O mojí galerii";
        }
        else {
            $articles = new ArticleDatabase();
            return $articles->getArticle($_GET["p"])["title"];
        }
    }
    else if (isset($_GET["cat"])&&isset($_GET["author"])) {
        $title = "Články v rubrice ".$_GET["cat"]." od ".$_GET["author"];
    }
    else if (isset($_GET["cat"])) {
        $title = "Články v rubrice ".$_GET["cat"];
    }
    else if (isset($_GET["author"])) {
        $title = "Články od ".$_GET["author"];
    }
    else if (!isset($_GET["p"])) {
            $title = "Domovská stránka";
    }
    return $title;
}