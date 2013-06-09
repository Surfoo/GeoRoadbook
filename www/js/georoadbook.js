$().ready(function() {

    // Config TinyMCE3.5.8
    tinyMCE.init({
        language: language,
        mode: "exact",
        theme_advanced_layout_manager: "SimpleLayout",
        elements: "editable",
        dialog_type: "modal",
        plugins: "fullpage,table,preview,pagebreak",
        theme_advanced_buttons1: 'fontselect,fontsizeselect,formatselect,|,forecolor,backcolor,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,bullist,numlist,|,outdent,indent,|,link,unlink',
        theme_advanced_buttons2: 'image,hr,removeformat,visualaid,|,sub,sup,charmap,pagebreak,|,tablecontrols,|,cleanup,code,fullpage,undo,redo',
        width: "210mm",
        height: "297mm",
        schema: "html4",
        content_css: "/design/roadbook.css",
        //theme_advanced_font_sizes: "10px,12px,13px,14px,16px,18px,20px",
        //theme_advanced_fonts: "Andale Mono=andale mono,times;Arial=arial,helvetica,sans-serif;Arial Black=arial black,avant garde;Book Antiqua=book antiqua,palatino;Comic Sans MS=comic sans ms,sans-serif;Courier New=courier new,courier;Georgia=georgia,palatino;Helvetica=helvetica;Impact=impact,chicago;Symbol=symbol;Tahoma=tahoma,arial,helvetica,sans-serif;Terminal=terminal,monaco;Times New Roman=times new roman,times;Trebuchet MS=trebuchet ms,geneva;Verdana=verdana,geneva;Webdings=webdings;Wingdings=wingdings,zapf dingbats",
        font_size_style_values: "10px,12px,13px,14px,16px,18px,20px",
        pagebreak_separator: "<p class=\"pagebreak\"><!-- pagebreak --></p>",
        visual: false,
        // Cleanup/Output
        apply_source_formatting: true
        //convert_fonts_to_spans: true,
        //convert_newlines_to_brs: false,
        //fix_list_elements: true,
        //fix_table_elements: true,
        //forced_root_block: 0,
        // fix empty alt attributes
        // extended_valid_elements: 'img[src|alt=|title|class|id|style|height|width]',
        //verify_html: false,


        // URL
        //relative_urls : false,
        //remove_script_host : true,
        /*setup: function(ed) {
            ed.onChange.add(function(ed, l) {
                ed.isNotDirty = false;
            });
        }*/
    });

    // Config TinyMCE4
    /*tinymce.init({
        selector: "#editable",
        language: language,
        height: "297mm",
        plugins: [
            "advlist autolink link image lists charmap hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking",
            "table contextmenu directionality emoticons template paste textcolor"],
        content_css: "../design/roadbook.css",
        menu: '',
        toolbar1: "undo redo | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent",
        toolbar2: "link image hr subscript superscript pagebreak table | code fullscreen",
        browser_spellcheck: true,
        pagebreak_separator: "<p class=\"pagebreak\"><!-- pagebreak --></p>",
    });*/

    $("#btn_delete").button().click(function() {
        if ($(this).hasClass('disabled')) {
            return;
        }
        if (!confirm('Are you sure to delete your roadbook?')) {
            return;
        }
        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);

        $.ajax({
            url: "/delete.php",
            type: "POST",
            datatype: 'json',
            data: {
                id: roadbook_id
            },
            success: function(data) {
                ed.setProgressState(0);

                if (!data || data === "") {
                    return;
                }
                if (typeof data !== 'object') {
                    return;
                }
                if (data && data.success) {
                    $(location).attr('href', '../?deleted');
                } else {
                    return;
                }
            },
            failure: function() {
                ed.setProgressState(0);
            }
        });
    });

    $('#ui_export').on('show', function() {
        if ($('#btn_export').hasClass('disabled')) {
            return;
        }
        $.getJSON('/roadbook/' + roadbook_id + '.json', function(data) {
            $.each(data, function(key, val) {
                //checkbox
                if ($('#' + key).is('input[type=checkbox]')) {
                    $('#' + key).attr("value", val);
                    if (val) {
                        $('#' + key).prop('checked', true);
                        $("#" + key.substr(0, 6) + "_text").prop('disabled', true);
                    }
                } else if ($('#' + key).is('select')) {
                    $('#' + key).val(val);
                    $('#' + key + ' option[value=' + val + ']').attr('selected', 'selected');
                } else if ($('#' + key).is('input')) {
                    $('#' + key).attr("value", val);
                }
            });
        });
    });

    $("#btn_save").click(function() {
        if ($(this).hasClass('disabled')) {
            return;
        }
        saveHtml();
    });

    $("#apply").click(function() {
        _ajax(false);
    });

    $("#export").click(function() {
        saveHtml();
        $('#ui_export').modal('hide');
        _ajax(true);
    });

    $("#header_pagination").click(function() {
        $("#header_text").prop('disabled', !$("#header_text").prop("disabled"));
    });

    $("#footer_pagination").click(function() {
        $("#footer_text").prop('disabled', !$("#footer_text").prop("disabled"));
    });

    function saveHtml() {
        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);
        $.ajax({
            url: "/save.php",
            type: "POST",
            async: false,
            datatype: 'json',
            data: {
                id: roadbook_id,
                content: ed.getContent()
            },
            success: function(data) {
                if (data && data.success) {
                    $('#btn_save').attr('title', data.last_modification);
                    ed.startContent = ed.getContent();
                    ed.isNotDirty = true;
                }
            },
            failure: function() {}
        });
        ed.setProgressState(0);
    }

    function _ajax(real_export) {
        tinymce.activeEditor.setProgressState(true);

        $('#btn_save').addClass('disabled');
        $('#btn_export').addClass('disabled');
        $('#btn_download_title').addClass('disabled');
        $('#btn_download').addClass('disabled');
        $('#btn_delete').addClass('disabled');

        $.ajax({
            url: "/export.php",
            type: "POST",
            data: {
                real_export: real_export,
                id: roadbook_id,
                'page-size': document.forms[0].page_size.value,
                'orientation': document.forms[0].orientation.value,
                'margin-left': document.forms[0].margin_left.value,
                'margin-right': document.forms[0].margin_right.value,
                'margin-top': document.forms[0].margin_top.value,
                'margin-bottom': document.forms[0].margin_bottom.value,
                'header-align': document.forms[0].header_align.value,
                'header-text': document.forms[0].header_text.value,
                'header-pagination': !! $('input[name="header_pagination"]:checked').val(),
                'footer-align': document.forms[0].footer_align.value,
                'footer-text': document.forms[0].footer_text.value,
                'footer-pagination': !! $('input[name="footer_pagination"]:checked').val(),
            },

            success: function(data) {
                tinymce.activeEditor.setProgressState(false);

                $('#btn_save').removeClass('disabled');
                $('#btn_export').removeClass('disabled');
                $('#btn_download_title').removeClass('disabled');
                $('#btn_download').removeClass('disabled');
                $('#btn_delete').removeClass('disabled');

                if (!real_export) {
                    return;
                }

                if (!data || data === "") {
                    alert('Conversion failed :-(');
                    return;
                }
                if (typeof data !== 'object') {
                    alert('Conversion failed :-(\nMessage:\n' + data);
                    return;
                }

                if (data && data.success) {
                    tinymce.activeEditor.setProgressState(false);
                    $('#dl_pdf').show();
                    $('#download_link').html(data.link + ' (' + data.size + 'Mo)');
                    $('#ui_exported').modal('show');
                } else {
                    alert('Conversion failed :-(\nMessage:\n' + data.error);
                }
            },
            failure: function() {
                tinymce.activeEditor.setProgressState(false);

                $('#btn_save').removeClass('disabled');
                $('#btn_export').removeClass('disabled');
                $('#btn_download_title').removeClass('disabled');
                $('#btn_download').removeClass('disabled');
                $('#btn_delete').removeClass('disabled');

                if (!real_export) {
                    return;
                }
                alert('Error in exportation.');
            }
        });
    }

});