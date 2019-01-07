<?php

/**
 * Class Database
 * Společná třída - zajišťuje spojení s databází.
 */
class Database
{
    private const SERVERNAME = "localhost";
    private const USERNAME = "xxxx";
    private const PASSWORD = "xxxx";

    /**
     * Database constructor.
     */
    public function __construct() {
        $link = $this->getLink();
        if (!$link) {
            die("Connection failed: " . mysqli_connect_error());
        }
    }

    /**
     * @return mysqli
     */
    public function getLink()
    {
        $link = new mysqli(self::SERVERNAME, self::USERNAME, self::PASSWORD, self::USERNAME);
        return $link;
    }
}

/**
 * Class UserDatabase
 * Třída s metodami pro obsluhu databáze uživatelů
 */
class UserDatabase extends Database {
    /**
     * @param $user
     * @return bool
     * Jestliže již uživatel existuje (tj. záznam s daným username má alespoň/právě jeden řádek), vrátí PRAVDA, jinak NEPRAVDA.
     */
    public function userExists($user) {
        $query = "SELECT *, BIN(`admin` + 0) AS `admin` FROM `users` WHERE username='$user'";
        $result = mysqli_query($this->getLink(), $query);
        if (mysqli_num_rows($result) != 0) {
            mysqli_free_result($result);
            mysqli_close($this->getLink());
            return true;
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return false;
    }

    /**
     * @param $username
     * @param $password
     * Vloží uživatele do db (sama o sobě ale data nekontroluje - validace probíhá v jiné části kódu).
     */
    public function insertUser ($username, $password) {
        $query = "INSERT INTO users (username, password, admin) VALUE ('$username', '$password', 0)";
        mysqli_query($this->getLink(), $query);
    }

    /**
     * @param $user
     * @return mixed
     * Vrátí hash pro uživatele (validace, zda existuje, probíhá jinde!)
     */
    private function getPasswordHash ($user) {
        $query = "SELECT password, BIN(`admin` + 0) AS `admin` FROM `users` WHERE username='$user'";
        $result = mysqli_query($this->getLink(), $query);
        $hash = mysqli_fetch_row($result)[0];
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $hash;
    }

    /**
     * @param $user
     * @return bool
     * Vrátí, zda má uživatel administrátorská práva (nevaliduje!)
     */
    private function isAdmin ($user) {
        $query = "SELECT admin, BIN(`admin` + 0) AS `admin` FROM `users` WHERE username='$user'";
        $result = mysqli_query($this->getLink(), $query);
        $admin = mysqli_fetch_row($result)[0];
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        if ($admin == 1) {
            $ret = true;
        }
        else {
            $ret = false;
        }
        return $ret;
    }

    /**
     * @param $username
     * @param $password
     * @return void
     * Validuje a přihlašuje uživatele.
     */
    public function signIn($username, $password) {
        if ($this->userExists($username)) {
            $hash = $this->getPasswordHash($username);
            if (password_verify($password, $hash)) {
                startUserSession($username, $this->isAdmin($username));
            }
        }
    }

    /**
     * @param $user
     * @param $new
     * Funkce pro změnu hesla v databázi.
     */
    public function changePassword($user, $new) {
        $query = "UPDATE users SET password = '$new' WHERE username=$user";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }
}


/**
 * @param $username
 * @return bool
 * kontrola délky
 */
function usernameValid($username) {
    if (strlen($username) < 3 || strlen($username) > 15) {
        return false;
    }
    return true;
}

/**
 * @param $password
 * @return bool
 * kontrola délky
 */
function passwordValid($password) {
    if (strlen($password) < 5 || strlen($password) > 20) {
        return false;
    }
    return true;
}

function passwordsMatch($password, $match) {
    if ($match != $password) {
        return false;
    }
    return true;
}

/**
 * @param $user
 * @param $admin
 * Nastavit session.
 */
function startUserSession($user, $admin) {
    $_SESSION["signed"] = true;
    $_SESSION["user"] = $user;
    $_SESSION["admin"] = $admin;
}

/**
 * Odhlásit uživatele - zrušit nastavení session.
 */
function signOut() {
    session_unset();
}

$db = new UserDatabase();

/**
 * Validace a přihlášení
 * */

$location = $_SERVER["PHP_SELF"];

if (isset($_POST["username"])&&isset($_POST["password"])&&!isset($_SESSION["signed"])) {
    header("Location: $location");
    $db->signIn($_POST["username"], $_POST["password"]);
}

/**
 * Jestliže jsou odeslána data v registračním formuláři, proběhne validace.
 * */
if (isset($_POST["reg_username"])&&isset($_POST["reg_password"])&&isset($_POST["reg_password_again"])&&!isset($_SESSION["signed"])) {
    if (!$db->userExists(mysqli_real_escape_string($db->getLink(), $_POST["reg_username"]))&&usernameValid($_POST["reg_username"])&&passwordsMatch($_POST["reg_password"], $_POST["reg_password_again"])&&passwordValid($_POST["reg_password"])) {
        header("Location: $location");
        $db->insertUser(mysqli_real_escape_string($db->getLink(), $_POST["reg_username"]), password_hash($_POST["reg_password"], PASSWORD_DEFAULT));
    }
}

if (isset($_POST["signout"])) {
    signOut();
    header("Location: $location");
}

/**
 * Class ArticleDatabase
 * Třída s metodami pro práci s články (a jejich zařazením do rubrik).
 */
class ArticleDatabase extends Database {

    /**
     * @param $title
     * @param $author
     * @param $contents
     * Vkládání článků do db.
     */
    public function insertArticle ($title, $author, $contents) {
        $query = "INSERT INTO articles (title, author, contents) VALUE ('$title', '$author', '$contents')";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $id
     * @param $title
     * @param $contents
     * AKtualizace existujícího článku podle identifikátoru.
     */
    public function updateArticle($id, $title, $contents) {
        $query = "UPDATE articles SET title = '$title', contents = '$contents' WHERE id=$id";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $id
     * Smazání čánku z db.
     */
    public function deleteArticle($id) {
        $query = "DELETE FROM articles WHERE id=$id";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @return array
     * Vrátí array se všemi články.
     */
    public function getAllArticles() {
        $articles = array();
        $query = "SELECT * FROM `articles`";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($articles, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $articles;
    }

    /**
     * @param $id
     * @return array|null
     * Vrátí buďto null (když článek neexistuje), nebo array s podrobnostmi o článku.
     */
    public function getArticle ($id) {
        $id = intval($id);
        if ($this->articleExists($id)) {
        $query = "SELECT * FROM `articles` WHERE id=$id";
        $result = mysqli_query($this->getLink(), $query);
        $article = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $article;
    }
    }

    /**
     * @return mixed
     * Vrací id posledního přidaného článku.
     */
    public function getLatestArticleId () {
        $query = "SELECT MAX(id) FROM `articles`";
        $result = mysqli_query($this->getLink(), $query);
        $latest = mysqli_fetch_row($result)[0];
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $latest;
    }

    /**
     * @param $article
     * @return bool
     * Kontrola, zda článek vůbec existuje.
     */
    public function articleExists ($article) {
        $article = intval($article);
        $query = "SELECT * FROM `articles` WHERE id=$article";
        $result = mysqli_query($this->getLink(), $query);
        if (mysqli_num_rows($result) === 0) {
            mysqli_free_result($result);
            mysqli_close($this->getLink());
            return false;
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return true;
    }

    /**
     * @param $id
     * @return array
     * Vrátí array se všemi rubrikami, v nichž článek je zařazen,
     */
    public function getArticleCategories($id) {
        if ($this->articleExists($id)) {
        $categories = array();
        $query = "SELECT category FROM `cat_art` WHERE article=$id";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($categories, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $categories;
    }
    }

    /**
     * @return array
     * Vrací array se všemi rubrikami, které existují.
     */
    public function getAllCategories() {
        $categories = array();
        $query = "SELECT category FROM `categories`";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($categories, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $categories;
    }

    /**
     * @param $category
     * @return bool
     * Kontrola, zda již kategorie existuje.
     */
    public function categoryExists ($category) {
        $query = "SELECT category FROM `categories` WHERE category='$category'";
        $result = mysqli_query($this->getLink(), $query);
        if (mysqli_num_rows($result) === 0) {
            mysqli_free_result($result);
            mysqli_close($this->getLink());
            return false;
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return true;
    }

    /**
     * @param $category
     * Přidání kategorie do tabulky categories
     */
    public function createCategory($category) {
        $query = "INSERT INTO `categories` (category) VALUE ('$category')";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $category
     * @param $article
     * Přidání článku do tabulky cat_art (podle názvu rubriky + id článku).
     */
    public function addCategoryRelation ($category, $article) {
        $query = "INSERT INTO cat_art (category, article) VALUE ('$category', $article)";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $category
     * @param $article
     * @return bool
     * Existuje relace mezi článkem a rubrikou?
     */
    public function relationExists($category, $article) {
        $query = "SELECT * FROM `cat_art` WHERE category='$category' AND article=$article";
        $result = mysqli_query($this->getLink(), $query);

        if (mysqli_num_rows($result) === 0) {
            mysqli_free_result($result);
            mysqli_close($this->getLink());
            return false;
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return true;
    }

    /**
     * @param $article
     * Odstraní všechny relace článek (podle id) : rubrika.
     */
    public function resetArticleRelations($article) {
        $query = "DELETE FROM cat_art WHERE article=$article";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $category
     * @return bool
     * Kontrola, zda je rubrika v relaci s nějakým článkem.
     */
    public function isCategoryToAny($category) {
        $query = "SELECT * FROM `cat_art` WHERE category='$category'";
        $result = mysqli_query($this->getLink(), $query);
        if (mysqli_num_rows($result) === 0) {
            mysqli_free_result($result);
            mysqli_close($this->getLink());
            return false;
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return true;
    }

    /**
     * @param $category
     * Smazání kategorie z tabulky categories.
     */
    public function deleteCategory($category) {
        $query = "DELETE FROM categories WHERE category='$category'";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $category
     * @return array
     * Výpis všech článků dané rubriky.
     */
    public function getArticlesByCategory ($category) {
        $articles = array();
        $query = "SELECT * FROM `cat_art` INNER JOIN articles ON articles.id=cat_art.article WHERE category='$category'";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($articles, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $articles;
    }

    /**
     * @param $author
     * @return array
     * Výpis všech článků daného autora.
     */
    public function getArticlesByAuthor ($author) {
        $articles = array();
        $query = "SELECT * FROM `articles` WHERE author='$author'";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($articles, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $articles;
    }

    /**
     * @param $category
     * @param $author
     * @return array
     * Výpis všech článků daného autora v dané rubrice.
     */
    public function getArticlesByCategoryAuthor ($category, $author) {
        $articles = array();
        $query = "SELECT * FROM `cat_art` INNER JOIN articles ON articles.id=cat_art.article WHERE category='$category' AND author='$author'";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($articles, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $articles;
    }
}

/**
 * @param $title
 * @return bool
 * Podle počtu znaků určuje, zda je titulek vhodný.
 */
function titleValid($title) {
    if ((strlen($title) > 0)&&(strlen($title) < 250)) {
        return true;
    }
    return false;
}

/**
 * @param $text
 * @return bool
 * Podle počtu znaků určuje, zda je článek vhodný.
 */
function textValid($text) {
    if ((strlen($text) > 0)&&(strlen($text) < 8000)) {
        return true;
    }
    return false;
}

$articles = new ArticleDatabase();


/**
 * @param $article
 * Kontrola $_POST['category']:
 * 1. Pole vyčistit od "", které se posílají jako hodnota hidden pole (případně nevyplněného pole).
 * 2. Vymazat všechny existující vazby v tabulce cat_art
 * 3. Zkontrolovat kategorie - dříve neexistující přidat do tabulky categories - poté přidat vazbu.
 * 4. Zkontrolovat, zda neexistuje nějaká kategorie bez vazby a případně ji smazat z categories.
 *
 */
function checkSubmittedArticleCategories ($article) {
    $articles = new ArticleDatabase();
    while (in_array("", $_POST["category"])) {
        $key = array_search("", $_POST["category"]);
        unset($_POST["category"][$key]);
    }
    $articles->resetArticleRelations($article);
    foreach ($_POST["category"] as $category) {
        if (!$articles->categoryExists($category)) {
            $articles->createCategory(mysqli_real_escape_string($articles->getLink(), $category));
        }
        $articles->addCategoryRelation($category, $article);
        foreach ($articles->getAllCategories() as $value) {
            if (!$articles->isCategoryToAny($value["category"])) {
                $articles->deleteCategory($value["category"]);
            }
    }
    }
}

/**
*   Následující větvení kontroluje, zda jsou odeslána článková data na server. Jestlli ano (a jsou validní), ukládá do db. (update nebo nový článek)
*/
if (isset($_POST["submit_article"])) {
    if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
        if (isset($_SESSION["user"]) && titleValid($_POST["edit_article_title"] && textValid($_POST["edit_article_text"]))) {
            header("Location: $location");
            if (isset($_POST["a"]) && $articles->articleExists($_POST["a"])) {
                $articles->updateArticle($_POST["a"], mysqli_real_escape_string($articles->getLink(), $_POST["edit_article_title"]), mysqli_real_escape_string($articles->getLink(), $_POST["edit_article_text"]));
                checkSubmittedArticleCategories($_POST["a"]);
            } else {
                $articles->insertArticle(mysqli_real_escape_string($articles->getLink(), $_POST["edit_article_title"]), mysqli_real_escape_string($articles->getLink(), $_SESSION["user"]), mysqli_real_escape_string($articles->getLink(), $_POST["edit_article_text"]));
                checkSubmittedArticleCategories($articles->getLatestArticleId());
            }
        }
    }
}

$comments = new CommentsDatabase();
/**
 * Mazání článku v db.
 * */
if (isset($_POST["delete"])&&isset($_POST["a"])) {
    if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
        $comments->deleteAllArticleComments($_POST["a"]);
        $articles->resetArticleRelations($_POST["a"]);
        $articles->deleteArticle($_POST["a"]);
    foreach ($articles->getAllCategories() as $category) {
        if (!$articles->isCategoryToAny($category["category"])) {
            $articles->deleteCategory($category["category"]);
        }
    }
    header("Location: $location");
}
}

/**
 * Class CommentsDatabase
 * Databáze všech komentářů
 */
class CommentsDatabase extends Database {
    /**
     * @param $article -- k jakému článku je komentář určen (id)
     * @param $author
     * @param $text
     * Vložení komentáře.
     */
    public function insertComment ($article, $author, $text) {
        $query = "INSERT INTO `comments` (article, author, text) VALUE ($article, '$author', '$text')";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $article
     * @return array
     * Vrátí array s komentáři k danému id článku.
     */
    public function getAllArticleComments($article) {
        $comments = array();
        $query = "SELECT * FROM `comments` WHERE article='$article'";
        $result = mysqli_query($this->getLink(), $query);
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($comments, $row);
        }
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $comments;
    }

    /**
     * @param $article
     * Smaže všechny komentáře podle id článku.
     */
    public function deleteAllArticleComments($article) {
        $query = "DELETE FROM comments WHERE article=$article";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $id
     * Smaže jeden komentář podle id.
     */
    public function deleteComment($id) {
        $query = "DELETE FROM comments WHERE id=$id";
        mysqli_query($this->getLink(), $query);
        mysqli_close($this->getLink());
    }

    /**
     * @param $id
     * @return mixed -- vrátí autora komentáře.
     */
    public function getCommentAuthor($id) {
        $query = "SELECT author FROM `comments` WHERE id=$id";
        $result = mysqli_query($this->getLink(), $query);
        $author = mysqli_fetch_row($result)[0];
        mysqli_free_result($result);
        mysqli_close($this->getLink());
        return $author;
    }
}


/**
 * @param $text
 * @return bool -- podle délky (0 nebo moc dlouhý není validní)
 */
function commentValid($text) {
    if (strlen($text) > 0 && strlen($text) < 900) {
        return true;
    }
    return false;
}
if (isset($_POST["submit_comment"])) {
    if (isset($_SESSION["user"])&&commentValid($_POST["comment_content"])) {
        $comments->insertComment($_GET["p"], mysqli_real_escape_string($comments->getLink(), $_SESSION["user"]), mysqli_real_escape_string($comments->getLink(), $_POST["comment_content"]));
        header("Location: $location?p=".$_GET["p"]);
    }
}

if (isset($_POST["deleteComment"])) {
    if (isset($_SESSION["user"])&&($_SESSION["user"] == $comments->getCommentAuthor($_POST["comment"]))||$_SESSION["admin"]) {
        $comments->deleteComment($_POST["comment"]);
        header("Location: $location?p=".$_GET["p"]);
    }
}
