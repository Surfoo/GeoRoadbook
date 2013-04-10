<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>Create your geocaching roadbook</title>
        <link rel="stylesheet" href="../css/blueprint/screen.css" type="text/css" media="screen, projection" />
        <script language="javascript" type="text/javascript" src="../lib/jquery-1.9.1.min.js"></script>
        <script language="javascript" type="text/javascript" src="../lib/tinymce/tiny_mce.js"></script>
        <script language="javascript" type="text/javascript">
            var roabook_id = '{$roabook_id|escape:js}', language = '{$language|escape:js}';
        </script>
        <script language="javascript" type="text/javascript" src="../lib/georoadbook.js"></script>
    </head>
    <body>
    <div id="tinymce" style="display: block !important; margin: auto; width: 210mm; height:100%;">
        <textarea id="editable" cols="100" rows="100">{$roabook_content}</textarea>
    </div>
</body>
</html>
