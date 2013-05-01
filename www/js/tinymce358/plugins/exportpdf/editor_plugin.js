(function() {
    // Load plugin specific language pack
    tinymce.PluginManager.requireLangPack('exportpdf');

    tinymce.create('tinymce.plugins.exportpdf', {
        init: function(ed, url) {
            ed.addCommand('exportPDF', function() {
                ed.windowManager.open({
                    file: url + '/dialog.html',
                    width: 430,
                    height: 400,
                    //resizable: "no",
                    //scrollbars: "no",
                    inline: 1,
                    //popup_css: ''
                }, {
                    plugin_url: url,
                    roadbook_id: roadbook_id
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