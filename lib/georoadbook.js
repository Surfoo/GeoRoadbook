$().ready(function() {
    tinyMCE.init({
        theme: "advanced",
        language: language,
        mode: "exact",
        elements: "editable",
        dialog_type: "modal",
        plugins: "fullpage,table,inlinepopups,preview,save,exportpdf,pagebreak",
        theme_advanced_buttons1: 'fontselect,fontsizeselect,formatselect,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,unlink',
        theme_advanced_buttons2: 'image,hr,removeformat,visualaid,|,sub,sup,charmap,pagebreak,|,tablecontrols,|,cleanup,code,fullpage,save,exportpdf',
        width: "210mm",
        height: $(window).height(),
        theme_advanced_layout_manager: "SimpleLayout",
        schema: "html5",
        content_css: "../css/mycontent.css?"/* + new Date().getTime()*/,
        theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
        theme_advanced_fonts: "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
        font_size_style_values: "10px,12px,13px,14px,16px,18px,20px",
        pagebreak_separator: "<p class=\"pagebreak\"><!-- pagebreak --></p>",
        save_enablewhendirty: true,
        save_onsavecallback: "save",
        forced_root_block : false,
        convert_fonts_to_spans : true,

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
    //save();
    var e = e || window.event;
    // For IE and Firefox
    if (e) {
        e.returnValue = '';
    }

    // For Safari
    return '';
};
