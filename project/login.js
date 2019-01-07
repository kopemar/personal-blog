var form = document.querySelector("#login");
form.addEventListener("submit", function(event){
    var username = document.querySelector("#user").value;
    var password = document.querySelector("#pass").value;
    if (username.length === 0) {
        alert("Vyplňte prosím uživatelské jméno.");
        event.preventDefault();
    }
    else if (password.length === 0) {
        alert("Vyplňte prosím heslo");
        event.preventDefault();
    }
});

var reg = document.querySelector("#register");
    reg.addEventListener("submit", function (event) {
        var username = document.querySelector("#reg_username").value;
        var password = document.querySelector("#reg_password").value;
        var passwordAgain = document.querySelector("#reg_password_again").value;
        if (username.length < 3 || username.length > 15) {
            alert("Uživatelské jméno by mělo mít mezi 3 a 15 znaky.");
            event.preventDefault();
        }
        else if (password.length < 5 || password.length > 20) {
            alert("Heslo by mělo mít mezi 5 a 20 znaky.");
            event.preventDefault();
        }
        else if (password !== passwordAgain) {
            alert("Hesla se musí shodovat.");
            event.preventDefault();
        }
    });
