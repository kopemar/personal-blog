
/*
 * Validace formuk
 */

var values = [];
var title = document.querySelector("#edit_article_title");
var text = document.querySelector("#edit_article_text");
var submitArticle = document.querySelector("#edit");
var addNewField = document.querySelector("#another_category");
var fields = document.querySelectorAll(".new_category_field");
addNewField.addEventListener("click", function () {
        for (var i = 0; i < fields.length; i++) {
            values.push(fields[i].value);
            if (fields[i].value.length === 0) {
                alert("Nelze přidat další pole, nejprve vyplňte všechna předchozí.");
                return;
            }
        }
        document.querySelector("#categories_fields").innerHTML += '<input type="text" name="category[]" class="new_category_field" placeholder="Add another category">';
        fields = document.querySelectorAll(".new_category_field");
        // restore values as they tend to disappear when rendering new input field
        for (var i = 0; i < fields.length - 1; i++) {
            fields[i].value = values.shift();
        }
    }
);

submitArticle.addEventListener("submit", function (event) {
    if (title.value.length === 0) {
        alert("Vyplňte prosím titulek");
        event.preventDefault();
    }
    else if (title.value.length > 250) {
        alert("Zkraťte prosím titulek");
        event.preventDefault();
    }
    else if (text.value.length === 0) {
        alert("Článek by měl mít obsah!");
        event.preventDefault();
    }
    else if (text.value.length > 8000) {
        alert("Zkraťte prosím článek!");
        event.preventDefault();
    }
});