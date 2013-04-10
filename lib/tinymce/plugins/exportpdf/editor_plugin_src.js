/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
    // Load plugin specific language pack
    tinymce.PluginManager.requireLangPack('exportpdf');

    tinymce.create('tinymce.plugins.exportpdf', {
        init: function(ed, url) {
            ed.addCommand('exportPDF', function() {
                save();
                if (tinyMCE.activeEditor.isDirty()) {
                    alert('Please save your roadbook before export it.');
                    return;
                }

                var ed = tinyMCE.get('editable');
                ed.setProgressState(1);
                $.ajax({
                    url: "../export.php",
                    type: "POST",
                    data: {
                        id: roabook_id,
                        content: ed.getContent(),
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
                                file: url + '/confirm.html',
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
            });
            ed.addButton('exportpdf', {
                title: 'Export PDF',
                image: url + '/pdf_icon.gif',
                cmd: 'exportPDF'

            });
        },

        /**
         * Creates control instances based in the incomming name. This method is normally not
         * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
         * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
         * method can be used to create those.
         *
         * @param {String} n Name of the control to create.
         * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
         * @return {tinymce.ui.Control} New control instance or null if no control was created.
         */
        /*createControl : function(n, cm) {
            return null;
        },*/

        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo: function() {
            return {
                longname: 'Export PDF',
                author: 'Surfoo',
                authorurl: 'https://github.com/Surfoo',
                infourl: 'https://github.com/Surfoo/georoadbook',
                version: '1'
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add('exportpdf', tinymce.plugins.exportpdf);
})();