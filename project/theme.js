/*
* Zmenšení záhlaví při scrollování.
* Zobrazení šipky nahoru při scrollování.
* */

let media = window.matchMedia("screen and (min-width: 800px)");
function scrollFn () {
    if (media.matches) {
    document.querySelector("#back_to_top").addEventListener("click", scrollUp);
    let scroll = document.documentElement.scrollTop;
    if ((scroll > 50)) {
        document.querySelector("header").classList.add("scroll")
    }
    else if ((scroll === 0)) {
        document.querySelector("header").classList.remove("scroll");
    }

    if (scroll > 100) {
        document.querySelector("#back_to_top").classList.add("top_display");
    }
    else {
        document.querySelector("#back_to_top").classList.remove("top_display");
    }
    }
}

/*
* Návrat zpět nahoru na stránku.
* */
function scrollUp () {
    document.documentElement.scrollTop = 0;
}

document.addEventListener("scroll", scrollFn);
