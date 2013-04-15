var ExportPdf = {
    preInit: function() {
        /*var url, t = tinyMCE,
            ed = t.activeEditor;*/

        tinyMCEPopup.requireLangPack();

        //console.log(ed.getParam('theme_advanced_fonts'));
    },
    exportpdf: function() {
        if (tinyMCE.activeEditor.isDirty()) {
            alert('Please save your roadbook before export it.');
            return;
        }
        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);
        $.ajax({
            url: "../../../../export.php",
            type: "POST",
            data: {
                id: tinyMCEPopup.getWindowArg('roabook_id'),
                content: ed.getContent(),
                'page-size': document.forms[0].page_size.value,
                'grayscale': document.forms[0].grayscale.checked,
                'orientation': document.forms[0].orientation.value,
                'margin-left': document.forms[0].margin_left.value,
                'margin-right': document.forms[0].margin_right.value,
                'margin-top': document.forms[0].margin_top.value,
                'margin-bottom': document.forms[0].margin_bottom.value,
                'header-left': document.forms[0].header_left.value,
                'header-center': document.forms[0].header_center.value,
                'header-right': document.forms[0].header_right.value,
                'header-line': document.forms[0].header_line.checked,
                'footer-left': document.forms[0].footer_left.value,
                'footer-center': document.forms[0].footer_center.value,
                'footer-right': document.forms[0].footer_right.value,
                'footer-line': document.forms[0].footer_line.checked,
            },

            success: function(data) {
                ed.setProgressState(0);
                if (!data || data === "") {
                    alert('Conversion failed :(');
                    return;
                }
                if (typeof data != 'object') {
                    alert('Conversion failed :(\nMessage:\n' + data);
                    return;
                }

                if (data && data.success) {
                    ed.windowManager.open({
                        file: tinyMCEPopup.getWindowArg('plugin_url') + '/download.html',
                        width: 230,
                        height: 70,
                        resizable: "no",
                        scrollbars: "no",
                        //popup_css : ed.baseURI.toAbsolute("themes/" + ed.settings.theme + "/skins/" + ed.settings.skin + "/content.css"),
                        inline: true
                    }, {
                        link: data.link,
                        size: data.size
                    });
                } else {
                    alert('Error.');
                }
            },
            failure: function(data) {
                ed.setProgressState(0);
                alert('Error.');
            }
        });
    }

};

ExportPdf.preInit();