$().ready(function() {
    tinyMCE.init({
        language: language,
        theme: "advanced",
        mode: "exact",
        theme_advanced_layout_manager: "SimpleLayout",
        elements: "editable",
        dialog_type: "modal",
        plugins: "fullpage,table,inlinepopups,preview,save,exportpdf,pagebreak",
        theme_advanced_buttons1: 'fontselect,fontsizeselect,formatselect,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,|,outdent,indent,|,link,unlink',
        theme_advanced_buttons2: 'image,hr,removeformat,visualaid,|,sub,sup,charmap,pagebreak,|,tablecontrols,|,cleanup,code,fullpage,undo,redo,save,exportpdf',
        width: "210mm",
        height: "297mm",
        schema: "html5",
        content_css: "../css/mycontent.css?",
        /* + new Date().getTime()*/
        inlinepopups_skin: "smskin1",
        //theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
        //theme_advanced_fonts: "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
        font_size_style_values: "10px,12px,13px,14px,16px,18px,20px",
        pagebreak_separator: "<p class=\"pagebreak\"><!-- pagebreak --></p>",
        visual: false,
        save_enablewhendirty: true,
        save_onsavecallback: "save",
        // Cleanup/Output
        //apply_source_formatting : true,
        convert_fonts_to_spans: true,
        convert_newlines_to_brs: false,
        fix_list_elements: true,
        fix_table_elements: true,
        fix_nesting: true,
        forced_root_block: 0,
        // fix empty alt attributes
        // extended_valid_elements: 'img[src|alt=|title|class|id|style|height|width]',
        verify_html: false,


        // URL
        //relative_urls : false,
        //remove_script_host : true,
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
            id: roadbook_id,
            content: ed.getContent()
        },
        success: function(data) {
            ed.setProgressState(0);
            ed.startContent = ed.getContent();
            ed.isNotDirty = true;
        },
        failure: function(data) {
            ed.setProgressState(0);
        }
    });
}

/*
function save2() {
    var ed = tinyMCE.get('editable');

    tinymce.util.XHR.send({
        url: "../save.php",
        content_type: "application/x-www-form-urlencoded",
        type: "POST",
        data: "content=" + escape(ed.getContent()) + "&id=" + escape(roadbook_id),
        success: function(text) {
            ed.setProgressState(0);
            ed.isNotDirty = true;
        },
        error: function(type, req, o) {
            ed.setProgressState(0);
        }
    });
    ed.setProgressState(1);
}*/

$(function() {
    $("#delete").button().click(function(event) {
        if (!confirm('Are you sure to delete your roadbook?')) {
            return;
        }
        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);

        $.ajax({
            url: "../delete.php",
            type: "POST",
            datatype: 'json',
            data: {
                roadbook: roadbook_id
            },
            success: function(data) {
                ed.setProgressState(0);

                if (!data || data === "") {
                    return;
                }
                if (typeof data != 'object') {
                    return;
                }
                if (data && data.success) {
                    $(location).attr('href', '../?deleted');
                } else {
                    return;
                }
            },
            failure: function(data) {
                ed.setProgressState(0);
            }
        });
    });
});
