var content_gpx = null;
showHintOptions();
showOrderOptions();

$('#hint').change(function() {
    showHintOptions();
});

$('#sort').change(function() {
    showOrderOptions();
});

$('.option-help').tooltip({
    placement: 'right'
});


function showHintOptions() {
    if ($('#hint').is(':checked')) {
        $('#hint_options').show();
    } else {
        $('#hint_options').hide();
    }
}

function showOrderOptions() {
    if ($('#sort').is(':checked')) {
        $('#sort_options').show();
    } else {
        $('#sort_options').hide();
    }
}
// file selection
if (window.File && window.FileList && window.FileReader) {

    $('#gpx').change(function(e) {

        // fetch FileList object
        var files = e.target.files || e.dataTransfer.files;

        // process all File objects
        for (var i = 0, f; f = files[i]; i++) {
            if (f.size > 8 * 1024 * 1024) {
                $('#error').html('<p>"' + f.name + '" is too big, 8Mo Max.<p>').show();
                return false;
            }
            ParseFile(f);
        }
    });
} else {
    $('#error').html('<p>Your browser doesn\'t support some features. Please, upgrade it.<br /> ' +
        'If you are using Internet Explorer 9, you must install the version 10 manually.</p>').show();
}
// output file information

function ParseFile(file) {
    var reader = new FileReader();
    var fileinfo = [{
            'name': file.name,
            'size': file.size
        }
    ];

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
        if (!doc ||  doc.documentElement.tagName != 'gpx') {
            content_gpx = null;
            $('#error').html('<p>"' + fileinfo[0]['name'] + '" in an invalid file.<p>').show().delay(3000).fadeOut();
            return false;
        }
        /*var count_caches = e.target.result.match(/<wpt/g);
        if (count_caches.length > 100) {
            content_gpx = null;
            $('#error').html('<p>' + count_caches.length + ' caches in the GPX File. The number of caches is limited to 100 because of limited resources.<p>').show();
            return false;
        }*/
        content_gpx = e.target.result;
    },
    reader.readAsText(file, 'UTF-8');
}

$('input[type="submit"]').click(function() {
    if (!content_gpx) {
        $('#error').html('<p>GPX file is missing.</p>').show().delay(3000).fadeOut();
        return false;
    }
    var btn = $(this);
    btn.button('loading');

    $.ajax({
        url: "upload.php",
        type: "POST",
        data: {
            gpx: content_gpx,
            locale: $('#locale').attr('selected', 'selected').val(),
            toc: !! $('input[name="toc"]:checked').val(),
            note: !! $('input[name="note"]:checked').val(),
            short_desc: !! $('input[name="short_desc"]:checked').val(),
            hint: !! $('input[name="hint"]:checked').val(),
            hint_encrypted: !! parseInt($('input[name="hint_encrypted"]:checked').val()),
            waypoints: !! $('input[name="waypoints"]:checked').val(),
            logs: !! $('input[name="logs"]:checked').val(),
            sort_by: $('input[name="sort_by"]:checked').val(),
            pagebreak: !! $('input[name="pagebreak"]:checked').val(),
            images: !! $('input[name="images"]:checked').val()
        },
        success: function(data) {
            if (!data || data === "" || typeof data != 'object') {
                return
            }
            if (data && !data.success) {
                $('#error').html('<p>' + data.message + '</p>').show();
                btn.button('reset');
                return
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
