$().ready(function() {
    tinyMCE.init({
        theme: "advanced",
        language: language,
        mode: "exact",
        elements: "editable",
        dialog_type: "modal",
        plugins: "fullpage,fullscreen,table,inlinepopups,print,preview,save,exportpdf,pagebreak",
        theme_advanced_buttons1: 'fontselect,fontsizeselect,formatselect,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink',
        theme_advanced_buttons2: 'image,hr,removeformat,visualaid,|,sub,sup,charmap,pagebreak,|,tablecontrols,|,cleanup,code,fullpage,fullscreen,print,save,exportpdf',
        width: "210mm",
        height: $(window).height(),
        theme_advanced_layout_manager: "SimpleLayout",
        schema: "html4",
        content_css: "../css/mycontent.css?" + new Date().getTime(),
        theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
        font_size_style_values: "10px,12px,13px,14px,16px,18px,20px",
        pagebreak_separator: "<div class=\"pagebreak\"><!-- pagebreak --></div>",
        save_enablewhendirty: false,
        save_onsavecallback: "save",
        setup: function(ed) {
            ed.onChange.add(function(ed, l) {
                ed.isNotDirty = false;
            });
        }
    });
});

function save() {
    var ed = tinyMCE.get('editable');
    ed.setProgressState(1);
    $.ajax({
        url: "../save.php",
        type: "POST",
        async: false,
        datatype: 'json',
        data: {
            id: roabook_id,
            content: ed.getContent(),
        },
        success: function(data) {
            ed.setProgressState(0);
            ed.startContent = ed.getContent({
                format: 'raw'
            });
            ed.isNotDirty = true;
        },
        failure: function(data) {
            ed.setProgressState(0);
        }
    });
}

window.onbeforeunload = function(e) {
    save();
    /*var e = e || window.event;
    // For IE and Firefox
    if (e) {
        e.returnValue = '';
    }

    // For Safari
    return '';*/
};


setInterval(save, 90000);