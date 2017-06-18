/* jshint esversion: 6 */
/* global $:true */

(function() {
    'use strict';

    $('#spoilers4gpx').click(function(event) {
        event.preventDefault();
        window.open(this.href);
    });

    var content_gpx = null;

    $('#btn_geocaching').click(function() {
        window.location = '/login';
    });

    $('#hint').change(function() {
        $('#hint_options').toggleClass('hidden');
    });

    $('#sort').change(function() {
        $('#sort_options').toggleClass('hidden');
    });

    $('.option-help').tooltip({
        placement: 'right'
    });

    // output file information
    function ParseFile(file) {
        var reader = new FileReader(),
            parser, doc, fileinfo = [{
                'name': file.name,
                'size': file.size
            }];

        reader.onload = function(e) {
                if (window.DOMParser) {
                    parser = new DOMParser();
                    doc = parser.parseFromString(e.target.result, 'text/xml');
                } else {
                    doc = new ActiveXObject('Microsoft.XMLDOM');
                    doc.async = 'false';
                    doc.loadXML(e.target.result);
                }

                doc = parser.parseFromString(e.target.result, 'application/xml');
                if (!doc || doc.documentElement.tagName != 'gpx') {
                    content_gpx = null;
                    $('#error').html('<p>"' + fileinfo[0].name + '" in an invalid file.<p>').show().delay(3000).fadeOut();
                    return false;
                }
                content_gpx = e.target.result;
            },
            /*jshint -W030 */
            reader.readAsText(file, 'UTF-8');
    }

    $('input[type="submit"]').click(function() {
        $('#error').html('').addClass('hidden');

        if ((!content_gpx && $('#pocket_list').attr('selected', 'selected').val() === 'undefined') || (!content_gpx && $('#pocket_list').attr('selected', 'selected').val() === '')) {
            $('#error').html('<p>A GPX file or a Pocket Query is missing.</p>').toggleClass('hidden');
            return false;
        }

        var btn = $(this);
        btn.button('loading');

        $.ajax({
            url: "/upload",
            type: "POST",
            data: {
                gpx: content_gpx,
                locale: $('#locale').attr('selected', 'selected').val(),
                toc: !!$('input[name="toc"]:checked').val(),
                note: !!$('input[name="note"]:checked').val(),
                short_desc: !!$('input[name="short_desc"]:checked').val(),
                long_desc: !!$('input[name="long_desc"]:checked').val(),
                hint: !!$('input[name="hint"]:checked').val(),
                hint_encrypted: !!parseInt($('input[name="hint_encrypted"]:checked').val()),
                waypoints: !!$('input[name="waypoints"]:checked').val(),
                spoilers: !!$('input[name="spoilers"]:checked').val(),
                logs: !!$('input[name="logs"]:checked').val(),
                sort_by: $('input[name="sort_by"]:checked').val(),
                pagebreak: !!$('input[name="pagebreak"]:checked').val(),
                images: !!$('input[name="images"]:checked').val(),
                pocket_guid: $('#pocket_list').attr('selected', 'selected').val()
            },
            success: function(data) {
                if (!data || data === "" || typeof data !== 'object') {
                    return;
                }
                if (data && !data.success) {
                    $('#error').html('<p>' + data.message + '</p>').show();
                    btn.button('reset');
                    return;
                }
                $(location).attr('href', data.redirect);
            },
            failure: function() {}
        });
        return false;
    });

    $('a[data-dismiss="fileupload"]').click(function() {
        content_gpx = null;
    });

    /**** Roadbook *****/
    if (document.getElementById('editable') !== null) {
        $().ready(function() {
            // Config TinyMCE 4
            tinymce.init({
                selector: "#editable",
                language: language,
                plugins: [
                    "advlist autolink link image lists charmap hr anchor pagebreak",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime nonbreaking",
                    "table contextmenu paste textcolor"
                ],
                content_css: "../design/roadbook.css",
                menubar: false,
                toolbar1: "undo redo | formatselect fontselect fontsizeselect | forecolor backcolor | bold italic underline strikethrough",
                toolbar2: "alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image hr table subscript superscript charmap pagebreak | code fullscreen",
                browser_spellcheck: true,
                pagebreak_separator: "<p class=\"pagebreak\"><!-- pagebreak --></p>",
                width: "210mm",
                height: "297mm",
                schema: "html4",
                apply_source_formatting: true,
                setup: function(editor) {
                    editor.on('change', function() {
                        $('#btn_save').removeClass('disabled').prop('disabled', false);
                    });
                }
            });
        });
    }

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
            url: "/delete",
            type: "POST",
            data: {
                id: roadbook_id
            },
            success: function(url) {
                location.href = url;
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

    var saveHtml = function() {
        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);
        $.ajax({
            url: "/save",
            type: "POST",
            datatype: 'json',
            data: {
                id: roadbook_id,
                content: ed.getContent()
            },
            beforeSend: function() {
                $('#btn_save').button('loading');
            },
            success: function(data) {
                if (data && data.success) {
                    $('#btn_save').attr('title', data.last_modification);
                    ed.startContent = ed.getContent();
                    ed.isNotDirty = true;
                }
            },
            complete: function() {
                $('#btn_save').addClass('disabled').prop('disabled', true).data('loading-text', 'Save').button('loading');
            },
            failure: function() {}
        });
        ed.setProgressState(0);
    };

    var _ajax = function(real_export) {
        var ed = tinyMCE.get('editable');
        ed.setProgressState(1);
        real_export = +real_export;

        $('#btn_save').addClass('disabled');
        $('#btn_export').addClass('disabled');
        $('#btn_download_title').addClass('disabled');
        $('#btn_download').addClass('disabled');
        $('#btn_delete').addClass('disabled');

        $.ajax({
            url: "/export",
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
                'header-pagination': !!$('input[name="header_pagination"]:checked').val(),
                'footer-align': document.forms[0].footer_align.value,
                'footer-text': document.forms[0].footer_text.value,
                'footer-pagination': !!$('input[name="footer_pagination"]:checked').val(),
            },

            success: function(data) {
                ed.setProgressState(0);

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
                    ed.setProgressState(0);
                    $('#dl_pdf').show();
                    $('#download_link').html(data.link + ' (' + data.size + 'Mb)');
                    $('#ui_exported').modal('show');
                } else {
                    alert('Conversion failed :-(\nMessage:\n' + data.error);
                }
            },
            failure: function() {
                ed.setProgressState(0);

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
    };

    // init
    if (typeof $("#gpx").fileinput === 'function') {
        $("#gpx")
            .fileinput({
                'browseLabel': 'GPX file',
                'showPreview': true,
                'showUpload': false,
                'maxFileCount': 1,
                'autoReplace': true,
                'maxFileSize': 8192,
                'allowedFileExtensions': ['gpx']
            })
            .on('change', function(e) {
                // fetch FileList object
                var files = e.target.files || e.dataTransfer.files;
                if (typeof files[0] === 'undefined') {
                    return false;
                }
                ParseFile(files[0]);
            });
    }
}());