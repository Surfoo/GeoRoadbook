var content_gpx = null;
showHintOptions();

$('#hint').change(function() {
    showHintOptions();
});

function showHintOptions() {
    if ($('#hint').is(':checked')) {
        $('#hint_options').show();
    } else {
        $('#hint_options').hide();
    }
}

// file selection
if (window.File && window.FileList && window.FileReader) {

    $('#gpx').change(function(e) {

        // fetch FileList object
        var files = e.target.files || e.dataTransfer.files;

        // process all File objects
        for (var i = 0, f; f = files[i]; i++) {
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
        if (!doc ||  doc.documentElement.tagName != 'gpx') {
            content_gpx = null;
            $('#error').html('<p>"' + fileinfo[0]['name'] + '" in an invalid file.<p>').show().delay(3000).fadeOut();
            return false
        }
        content_gpx = e.target.result;

    },
    reader.readAsText(file, 'UTF-8');
}

$('input[type="submit"]').click(function() {
    if (!content_gpx) {
        $('#error').html('<p>GPX file is missing.</p>').show().delay(3000).fadeOut();
        return false
    }

    var locale = $('#locale').attr('selected', 'selected').val();
    var tidy = !! $('input[name="tidy"]:checked').val();
    var toc = !! $('input[name="toc"]:checked').val();
    var note = !! $('input[name="note"]:checked').val();
    var short_desc = !! $('input[name="short_desc"]:checked').val();
    var hint = !! $('input[name="hint"]:checked').val();
    var hint_encrypted = !! parseInt($('input[name="hint_encrypted"]:checked').val());
    var logs = !! $('input[name="logs"]:checked').val();

    $.ajax({
        url: "upload.php",
        type: "POST",
        data: {
            gpx: content_gpx,
            locale: locale,
            tidy: tidy,
            toc: toc,
            note: note,
            short_desc: short_desc,
            hint: hint,
            hint_encrypted: hint_encrypted,
            logs: logs
        },
        success: function(data) {
            if (!data || data === "" || typeof data != 'object') {
                return
            }
            if (data && !data.success) {
                $('#error').html('<p>' + data.message + '</p>').show();
                return
            }
            $(location).attr('href', data.redirect);
        },
        failure: function(data) {}
    });
    return false;
});

$('a[data-dismiss="fileupload"]').click(function() {
    content_gpx = null;
});
