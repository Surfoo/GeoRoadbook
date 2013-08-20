$().ready(function() {

    // Config TinyMCE 4
    tinymce.init({
        selector: "#editable",
        language: language,
        height: "297mm",
        plugins: [
            "advlist autolink link image lists charmap hr anchor pagebreak",
            "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking",
            "table contextmenu paste textcolor"],
        content_css: "../design/roadbook.css",
        menubar : false,
        toolbar1: "undo redo | formatselect fontselect fontsizeselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify",
        toolbar2: "bullist numlist outdent indent | link image hr table subscript superscript charmap pagebreak | code fullscreen",
        browser_spellcheck: true,
        pagebreak_separator: "<p class=\"pagebreak\"><!-- pagebreak --></p>",
        width: "210mm",
        height: "297mm",
        schema: "html4",
        apply_source_formatting: true,
    });

    $("#btn_delete").button().click(function() {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        if (!confirm('Are you sure to delete your roadbook?')) {
            return false;
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
            return false;
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
            return false;
        }
        var btn = $(this);
        btn.button('loading');
        saveHtml();
        btn.button('reset');
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