var ExportPdf = {
    preInit: function() {
        tinyMCEPopup.requireLangPack();
    },
    exportpdf: function() {
        if (tinyMCE.activeEditor.isDirty()) {
            alert('Please save your roadbook before export it.');
            return;
        }
        $('#insert').prop('disabled', true);
        ExportPdf._ajax(true);

    },
    apply: function() {
        ExportPdf._ajax(false);
    },
    _ajax: function(real_export) {

        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);
        $.ajax({
            url: "../../../../export.php",
            type: "POST",
            data: {
                real_export: real_export,
                id: tinyMCEPopup.getWindowArg('roadbook_id'),
                content: ed.getContent(),
                'page-size': document.forms[0].page_size.value,
                'orientation': document.forms[0].orientation.value,
                'margin-left': document.forms[0].margin_left.value,
                'margin-right': document.forms[0].margin_right.value,
                'margin-top': document.forms[0].margin_top.value,
                'margin-bottom': document.forms[0].margin_bottom.value,
                'header-left': document.forms[0].header_left.value,
                'header-center': document.forms[0].header_center.value,
                'header-right': document.forms[0].header_right.value,
                // 'header-line': document.forms[0].header_line.checked,
                // 'header-spacing': document.forms[0].header_spacing.value,
                'footer-left': document.forms[0].footer_left.value,
                'footer-center': document.forms[0].footer_center.value,
                'footer-right': document.forms[0].footer_right.value,
                // 'footer-line': document.forms[0].footer_line.checked,
                // 'footer-spacing': document.forms[0].footer_spacing.value,
            },

            success: function(data) {
                ed.setProgressState(0);
                if (!real_export) {
                    return;
                }
                $('#insert').prop('disabled', false);
                if (!data || data === "") {
                    alert('Conversion failed :(');
                    return;
                }
                if (typeof data != 'object') {
                    alert('Conversion failed :(\nMessage:\n' + data);
                    return;
                }

                if (data && data.success) {
                    $(window.parent.document).find('#dl_pdf').show();
                    ed.windowManager.open({
                        file: tinyMCEPopup.getWindowArg('plugin_url') + '/download.html',
                        width: 230,
                        height: 70,
                        resizable: "no",
                        scrollbars: "no",
                        inline: true
                    }, {
                        link: data.link,
                        size: data.size
                    });
                } else {
                    alert('Conversion failed :(\nMessage:\n' + data.error);
                }
            },
            failure: function(data) {
                ed.setProgressState(0);
                if (!real_export) {
                    return;
                }
                $('#insert').prop('disabled', false);
                alert('Error in exportation.');
            }
        });
    }

};

ExportPdf.preInit();

$().ready(function() {
    $.getJSON('../../../../roadbook/' + tinyMCEPopup.getWindowArg('roadbook_id') + '.json', function(data) {
        $.each(data, function(key, val) {
            if ($('#' + key).is('input')) {
                $('#' + key).attr("value", val);
            } else if ($('#' + key).is('select')) {
                $('#' + key + ' option[value=' + val + ']').attr('selected', 'selected');
            }
        });
    });
});