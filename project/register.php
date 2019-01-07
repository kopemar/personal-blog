
<?php
/**
 * Statická stránka - registrace uživatele.
 */
$db = new UserDatabase();
if (!isset($_SESSION["signed"])) {?>
<h2>Registrace</h2>
<form method="post" action="<?php echo ($_SERVER["PHP_SELF"]).'?p='.urlencode($_GET["p"])?>" id="register" novalidate>
    <label> Uživatelské jméno(*): <input name="reg_username" type="text"
                                value="<?php if (isset($_POST["reg_username"])) {echo $_POST['reg_username'];}?>"
                                         id="reg_username"
                                required><br>
    </label>
    <label> Heslo (*): <input name="reg_password" type="password" id="reg_password" required > <br>
    </label>
    <label> Kontrola hesla (*):
        <input name="reg_password_again" type="password" id="reg_password_again" required><br>
    </label>
    <?php if (isset($_POST["register"])) {
        if ($db->userExists($_POST["reg_username"])) { ?>
            <div class="error_message">Uživatelské jméno je již obsazené. </div>
        <?php }
        else if (!usernameValid($_POST["reg_username"])) { ?>
            <div class="error_message">Uživatelské jméno musí mít mezi 3 a 15 znaky.</div>
        <?php }
        else if (!passwordValid($_POST["reg_password"])) { ?>
            <div class="error_message">Heslo musí mít mezi 5 a 20 znaky.</div>
        <?php }
         else if (!passwordsMatch($_POST["reg_password"], $_POST["reg_password_again"])) { ?>
            <div class="error_message">Hesla se neshodují.</div>
        <?php }
    } ?>
    <button type="submit" name="register">Register</button>

</form>
<?php }
else {
?>
<h2>Už jste přihlášeni</h2>
<p>
    Přihlášený uživatel se nemůže znovu registrovat.
</p>
<?php }?>
