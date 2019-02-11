<?php

/**
 * Class Database
 * Constructs connection to DB (via PDO).
 */
class Database
{

    protected $link;

    public function __construct() {
        try {
            $this->link = new PDO("mysql:host=xx; dbname=xx", "xx", "xx");
        }
        catch (PDOException $e) {
            print "Error!: " . $e->getMessage() . "<br/>";
            die();
        }
    }
}

/**
 * Class UserDatabase
 */
class UserDatabase extends Database {
    /**
     * @param $user
     * @return bool
     */
    public function userExists($user) {
        $query = $this->link->prepare("SELECT *, BIN(`admin` + 0) AS `admin` FROM `users` WHERE username=:user");
        $query->bindParam(":user", $user);
        $query->execute();
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $username
     * @param $password
     * insert user into db (without validation, as a regular user)
     */
    public function insertUser ($username, $password) {
        $query = $this->link->prepare("INSERT INTO users (username, password, admin) VALUES (:username, :password, 0)");
        $query->bindParam(":username", $username);
        $query->bindParam(":password", password_hash($password, PASSWORD_DEFAULT));
        $query->execute();
    }

    /**
     * @param $user
     * @return string
     * returns hashed password
     */
    public function getPasswordHash ($user) {
        $query = $this->link->prepare("SELECT * FROM `users` WHERE username = :username");
        $query->bindParam(":username", $user);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]["password"];
    }

    /**
     * @param $user
     * @return bool
     * returns bool - if the user is an admin or not
     */
    public function isAdmin ($user) {
        $query = $this->link->prepare("SELECT * FROM `users` WHERE username = :username");
        $query->bindParam(":username", $user);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);
        if ($result[0]["admin"] == 1) {
            return true;
        }
        return false;
    }

    /**
     * @param $username
     * @param $password
     * validate user and sign him in
     */
    public function signIn($username, $password) {
        if ($this->userExists($username)) {
            $hash = $this->getPasswordHash($username);
            if (password_verify($password, $hash)) {
                startUserSession($username, $this->isAdmin($username));
                $location = $_SERVER["PHP_SELF"];
                header("Location: $location");
            }
        }
    }
}


/**
 * @param $username
 * @return bool
 * length check
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
 * length check
 */
function passwordValid($password) {
    if (strlen($password) < 5) {
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
 * set user session
 */
function startUserSession($user, $admin) {
    $_SESSION["signed"] = true;
    $_SESSION["user"] = $user;
    $_SESSION["admin"] = $admin;
}

/**
 * sign out -- unset session
 */
function signOut() {
    session_unset();
}

$db = new UserDatabase();

$location = $_SERVER["PHP_SELF"];

/**
 * if a sign-in form is sent, try to sign the user in
 */
if (isset($_POST["username"])&&isset($_POST["password"])&&!isset($_SESSION["signed"])) {
    $db->signIn($_POST["username"], $_POST["password"]);
}

/**
 * registration - validate data and register new user
 * */
if (isset($_POST["reg_username"])&&isset($_POST["reg_password"])&&isset($_POST["reg_password_again"])&&!isset($_SESSION["signed"])) {
    if (!$db->userExists($_POST["reg_username"])&&usernameValid($_POST["reg_username"])&&passwordsMatch($_POST["reg_password"], $_POST["reg_password_again"])&&passwordValid($_POST["reg_password"])) {
        header("Location: $location");
        $db->insertUser($_POST["reg_username"], $_POST["reg_password"]);
        startUserSession($_POST["reg_username"], false);
    }
}

if (isset($_POST["signout"])) {
    signOut();
    header("Location: $location");
}

/**
 * Class ArticleDatabase
 */
class ArticleDatabase extends Database {

    /**
     * @param $title
     * @param $author
     * @param $contents
     */
    public function insertArticle ($title, $author, $contents) {
        $query = $this->link->prepare("INSERT INTO `articles` (`title`, `author`, `contents`) VALUES (:title, :author, :contents)");
        $query->bindParam(":title", $title);
        $query->bindParam(":author", $author);
        $query->bindParam(":contents", $contents);
        $query->execute();
    }

    /**
     * @param $id
     * @param $title
     * @param $contents
     */
    public function updateArticle($id, $title, $contents) {
        $query = $this->link->prepare("UPDATE articles SET title = :title, contents = :contents WHERE id=:id");
        $query->bindParam(":title", $title);
        $query->bindParam(":contents", $contents);
        $query->bindParam(":id", $id);
        $query->execute();
    }

    /**
     * @param $id
     * Smazání čánku z db.
     */
    public function deleteArticle($id) {
        $this->link->query("DELETE FROM articles WHERE id=$id");
    }

    /**
     * @return array
     */
    public function getAllArticles() {
        $stmt = $this->link->prepare("SELECT * FROM `articles`");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return array|null
     */
    public function getArticle ($id) {
        $id = intval($id);
        if ($this->articleExists($id)) {
            $query = $this->link->prepare("SELECT * FROM `articles` WHERE id=:id");
            $query->bindParam(":id", $id);
            $query->execute();
            $article = $query->fetchAll(PDO::FETCH_ASSOC);
            return $article[0];
    }
    return null;
    }

    /**
     * @return mixed
     */
    public function getLatestArticleId () {
        $query = $this->link->query("SELECT MAX(id) FROM `articles`");
        $latest = $query->fetchAll(PDO::FETCH_ASSOC)[0]["id"];
        return $latest;
    }

    /**
     * @param $article
     * @return bool
     * Kontrola, zda článek vůbec existuje.
     */
    public function articleExists ($article) {
        $article = intval($article);
        $query = $this->link->prepare("SELECT * FROM `articles` WHERE id=$article");
        $query->execute();
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $id
     * @return array
     */
    public function getArticleCategories($id) {
        if ($this->articleExists($id)) {
            $query = $this->link->prepare("SELECT category FROM `cat_art` WHERE article=$id");
            $query->execute();
            $result = $query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
    }

    /**
     * @return array
     */
    public function getAllCategories() {
        $query = $this->link->prepare("SELECT category FROM `categories`");
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $category
     * @return bool

     */
    public function categoryExists ($category) {
        $query = $this->link->prepare("SELECT category FROM `categories` WHERE category=:category");
        $query->bindParam(":category", $category);
        $query->execute();
        if ($query->rowCount() === 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $category
     */
    public function createCategory($category) {
        $query = $this->link->prepare("INSERT INTO `categories` (category) VALUE (:category)");
        $query->bindParam(":category", $category);
        $query->execute();
    }

    /**
     * @param $category
     * @param $article
     */
    public function addCategoryRelation ($category, $article) {
        $query = $this->link->prepare("INSERT INTO cat_art (category, article) VALUE (:category, :article)");
        $query->bindParam(":category", $category);
        $query->bindParam(":article", $article);
        $query->execute();
    }

    /**
     * @param $category
     * @param $article
     * @return bool
     */
    public function relationExists($category, $article) {

        $query = $this->link->prepare("SELECT * FROM `cat_art` WHERE category=:category AND article=:article");
        $query->bindParam(":category", $category);
        $query->bindParam(":article", $article);
        $query->execute();
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $article
     */
    public function resetArticleRelations($article) {
        $this->link->query("DELETE FROM cat_art WHERE article=$article");
    }

    /**
     * @param $category
     * @return bool
 
     */
    public function isCategoryToAny($category) {
        $query = $this->link->prepare("SELECT * FROM `cat_art` WHERE category=:category");
        $query->bindParam(":category", $category);
        $query->execute();
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * @param $category
     */
    public function deleteCategory($category) {
        $query = $this->link->prepare("DELETE FROM `categories` WHERE `category` = :category");
        $query->bindParam(":category", $category);
        $query->execute();
    }

    /**
     * @param $category
     * @return array
     */
    public function getArticlesByCategory ($category) {
        $query = $this->link->prepare("SELECT * FROM `cat_art` INNER JOIN articles ON articles.id=cat_art.article WHERE category=:category");
        $query->bindParam(":category", $category);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $author
     * @return array
     */
    public function getArticlesByAuthor ($author) {
        $query = $this->link->prepare("SELECT * FROM `articles` WHERE author=:author");
        $query->bindParam(":author", $author);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $category
     * @param $author
     * @return array
     */
    public function getArticlesByCategoryAuthor ($category, $author) {
        $query = $this->link->prepare("SELECT * FROM `cat_art` INNER JOIN articles ON articles.id=cat_art.article WHERE category=:category AND author=:author");
        $query->bindParam(":author", $author);
        $query->bindParam(":category", $category);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}

/**
 * @param $title
 * @return bool
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
 */
function textValid($text) {
    if ((strlen($text) > 0)&&(strlen($text) < 8000)) {
        return true;
    }
    return false;
}

$articles = new ArticleDatabase();

function categoriesLengthCheck ($array) {
    foreach ($array as $value) {
        if (strlen($value) > 100) {
            return false;
        }
    }
    return true;
}

/**
 * @param $article
 * Check $_POST['category']:
 * 1. Clean all blank categories ("") -- they are sent in hidden field in article form
 * 2. Clean all existing relations in cat_art
 * 3. Check all existing categories - if there is some that wasn't in db before, add it.
 * 4. Check all categories in table categories - if there is any without any relation to article, delete it.  
 */

function checkSubmittedArticleCategories ($article) {
    $articles = new ArticleDatabase();
    while (in_array("", $_POST["category"])) {
        $key = array_search("", $_POST["category"]);
        unset($_POST["category"][$key]);
    }
    if (categoriesLengthCheck($_POST["category"])) {
    $articles->resetArticleRelations($article);
    foreach ($_POST["category"] as $category) {
        if (!$articles->categoryExists($category)) {
            $articles->createCategory($category);
        }
        $articles->addCategoryRelation($category, $article);
    }
    }
    foreach ($articles->getAllCategories() as $value) {
        if (!$articles->isCategoryToAny($value["category"])) {
            $articles->deleteCategory($value["category"]);
        }
    }
}

/**
*   New article is sent -- validate and call function to add it to db if it's valid. 
*/
if (isset($_POST["submit_article"])) {
    if (isset($_SESSION["admin"]) && $_SESSION["admin"]) {
        if (isset($_SESSION["user"]) && titleValid($_POST["edit_article_title"] && textValid($_POST["edit_article_text"])) && categoriesLengthCheck($_POST["category"])) {
            header("Location: $location");
            if (isset($_POST["a"]) && $articles->articleExists($_POST["a"])) {
                    checkSubmittedArticleCategories($_POST["a"]);
                    $articles->updateArticle($_POST["a"],  $_POST["edit_article_title"], $_POST["edit_article_text"]);
            } else {
                $articles->insertArticle($_POST["edit_article_title"], $_SESSION["user"], $_POST["edit_article_text"]);
                checkSubmittedArticleCategories($articles->getLatestArticleId());
            }
        }
    }
}

$comments = new CommentsDatabase();
/**
 * Delete article in db.
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
 */
class CommentsDatabase extends Database {
    /**
     * @param $article -- id of the article
     * @param $author
     * @param $text
     * Vložení komentáře.
     */
    public function insertComment ($article, $author, $text) {
        $query = $this->link->prepare("INSERT INTO `comments` (article, author, text) VALUE (:article, :author, :text)");
        $query->bindParam(":article", $article);
        $query->bindParam(":author", $author);
        $query->bindParam(":text", $text);
        $query->execute();
    }

    /**
     * @param $article
     * @return array
 
     */
    public function getAllArticleComments($article) {
        $query = $this->link->prepare("SELECT * FROM `comments` WHERE article=:article");
        $query->bindParam(":article", $article);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $article
     */
    public function deleteAllArticleComments($article) {
        $this->link->query("DELETE FROM comments WHERE article=$article");

    }

    /**
     * @param $id
     */
    public function deleteComment($id) {
        $this->link->query("DELETE FROM comments WHERE id=$id");
    }

    /**
     * @param $id
     * @return string
     */
    public function getCommentAuthor($id) {
        $query = $this->link->prepare("SELECT * FROM `comments` WHERE id=:id");
        $query->bindParam(":id", $id);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC)[0]["author"];
    }
}


/**
 * @param $text
 * @return bool
 */
function commentValid($text) {
    if (strlen($text) > 0 && strlen($text) < 900) {
        return true;
    }
    return false;
}
if (isset($_POST["submit_comment"])) {
    if (isset($_SESSION["user"])&&commentValid($_POST["comment_content"])) {
        $comments->insertComment($_GET["p"], $_SESSION["user"],  $_POST["comment_content"]);
        header("Location: $location?p=".$_GET["p"]);
    }
}

if (isset($_POST["deleteComment"])) {
    if (isset($_SESSION["user"])&&($_SESSION["user"] == $comments->getCommentAuthor($_POST["comment"]))||$_SESSION["admin"]) {
        $comments->deleteComment($_POST["comment"]);
        header("Location: $location?p=".$_GET["p"]);
    }
}
