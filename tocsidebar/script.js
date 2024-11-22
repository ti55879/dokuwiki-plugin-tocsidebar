jQuery(document).ready(function () {
    console.log('DEBUG: Executing JS: jQuery(document).ready(function () {');
    console.log("Script loaded and ready!");

    var toc_title = jQuery("span.tocsel_title").html();
    jQuery("#tocseltoggle img").css('cursor', 'pointer');

    jQuery("#selectoc_btn").click(function () {
        console.log('DEBUG: Executing JS: jQuery("#selectoc_btn").click(function () {');
        var file = this.form.selectoc_id.value;
        console.log(file);
        jQuery("#setctoc_out").css('display', 'block');
        if (file.match(/:\*$/)) {
            jQuery("span.tocsel_title").html('Index');
        } else {
            jQuery("span.tocsel_title").html(toc_title);
        }
        var params = "seltoc_val=" + encodeURIComponent(file);
        params += '&call=tocsidebar';

        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php', params,
            function (data) {
                console.log('DEBUG: Executing JS: function (data) {');
                if (!data) {
                    document.getElementById("setctoc_out").innerHTML = "";
                    console.log('DEBUG: Executing JS: document.getElementById("setctoc_out").innerHTML = "";');
                } else {
                    document.getElementById("setctoc_out").innerHTML = data;
                    console.log('DEBUG: Executing JS: document.getElementById("setctoc_out").innerHTML = data;');
                }
            },
            'html'
        );

        jQuery("li").off("click").click(function () {
            console.log('DEBUG: Executing JS: jQuery("li").off("click").click(function () {');
            var a = jQuery("#selectoc_id");
            a.attr('title', a.attr('value'));
        });

        jQuery("#tocsel_rootns").click(function () {
            console.log('DEBUG: Executing JS: jQuery("#tocsel_rootns").click(function () {');
            var a = jQuery("#selectoc_id");
            a.attr('title', a.attr('value'));
        });

        jQuery("#tocseltoggle img").off("click").click(function () {
            console.log('DEBUG: Executing JS: jQuery("#tocseltoggle img").off("click").click(function () {');
            jQuery("#setctoc_out").toggle();
            var dir = DOKU_BASE + 'lib/plugins/tocsidebar/img/';
            var curSrc = jQuery(this).attr('src');
            if (curSrc.match(/open/)) {
                jQuery(this).attr('src', dir + 'closed.png');
            }
            if (curSrc.match(/closed/)) {
                jQuery(this).attr('src', dir + 'open.png');
            }
        });
    });

    function ini_textbox(name) {
        console.log('DEBUG: Executing JS: function ini_textbox(name) {');
        var a = jQuery("#selectoc_id");
        a.attr('title', name);
        console.log();
    }

    var dom = document.getElementById("selectoc_id");
    console.log('DEBUG: Executing JS: var dom = document.getElementById("selectoc_id");');
    if (dom && dom.value.match(/curID/)) {
        dom.value = JSINFO['id'];
        jQuery("#selectoc_btn").click();
        ini_textbox(JSINFO['id']);
    } else {
        var cval = tocsel_getCookie('tocselect');
        if (cval && document.getElementById("selectoc_id")) {
            console.log('DEBUG: Executing JS: if (cval && document.getElementById("selectoc_id")) {');
            cval = cval.replace(/%3A/g, ':');
            document.getElementById("selectoc_id").value = cval;
            console.log('DEBUG: Executing JS: document.getElementById("selectoc_id").value = cval;');
            jQuery("#selectoc_btn").click();
            ini_textbox(cval);
        }
    }

    // Hover-Effekt nur für die innerste sichtbare Box
    jQuery("#setctoc_out").on("mouseenter", "li:visible", function () {
        console.log('DEBUG: Executing JS: jQuery("#setctoc_out").on("mouseenter", "li:visible", function () {');
        console.log("Hovering over:", this);
        console.log("Is this the inner-most visible li?", !jQuery(this).find("li:visible").length);
        // Entfernt den Effekt von allen anderen Boxen
        jQuery("#setctoc_out .hover-active").removeClass("hover-active");

        // Überprüfen, ob es sich um die innerste sichtbare Box handelt
        if (!jQuery(this).find("li:visible").length) {
            jQuery(this).addClass("hover-active");
            console.log("Added hover-active to:", this);
        }
    });

    jQuery("#setctoc_out").on("mouseleave", "li:visible", function () {
        console.log('DEBUG: Executing JS: jQuery("#setctoc_out").on("mouseleave", "li:visible", function () {');
        console.log("Mouse left:", this);
        // Entfernt den Hover-Effekt, wenn die Maus den Bereich verlässt
        jQuery(this).removeClass("hover-active");
    });



    // Verstecken der Sidebar, wenn kein TOC vorhanden ist
    var sidebar = document.getElementById("sidebar-container");
    console.log('DEBUG: Executing JS: var sidebar = document.getElementById("sidebar-container");');
    if (sidebar && sidebar.classList.contains("no-toc")) {
        console.log("Hiding sidebar due to no TOC.");
        sidebar.style.display = "none";

        // Passt die Breite des Hauptinhalts an
        var content = document.getElementById("content");
        console.log('DEBUG: Executing JS: var content = document.getElementById("content");');
        if (content) {
            content.style.width = "100%";
        }
    }
});
function tocsel_updatetoc(name) {
    console.log('DEBUG: Executing JS: function tocsel_updatetoc(name) {');
    var dom = document.getElementById("selectoc_id");
    console.log('DEBUG: Executing JS: var dom = document.getElementById("selectoc_id");');
    dom.value = name;
    jQuery("#selectoc_btn").click();
}

function tocsel_getCookie(cname) {
    console.log('DEBUG: Executing JS: function tocsel_getCookie(cname) {');
    var name = cname + "=";
    var ca = document.cookie.split(';');
    console.log('DEBUG: Executing JS: var ca = document.cookie.split(";");');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}
