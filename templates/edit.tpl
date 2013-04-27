<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>GeoRoadBook, create your geocaching roadbook</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" media="all" />
        <link href="../css/design.css" rel="stylesheet" media="screen">
        <script type="text/javascript" src="../lib/jquery-2.0.0.min.js"></script>
        <script type="text/javascript" src="../lib/tinymce358/tiny_mce.js"></script>
        <script type="text/javascript">
            var roadbook_id = '{$roadbook_id|escape:js}', language = '{$language|escape:js}';
        </script>
        
    </head>
    <body>
        <div class="container">
            <div class="hero-unit">
                {include file="header.tpl"}

                <div id="ui_edition">
                    <div>
                        <div class="pull-right">
                            <button type="submit" id="delete" class="btn btn-warning"><i class="icon-trash icon-white"></i> Delete</button>
                        </div>
                        <ul id="download">
                            <li><a href="?raw" class="btn btn-info" title="View only as HTML page"><i class="icon-file icon-white"></i> HTML</a></li>
                            {if isset($available_zip)}<li><a href="?zip" class="btn btn-info" title="Download a zip archive (HTML and images)"><i class="icon-download icon-white"></i> ZIP</a></li>{/if}
                            <li id="dl_pdf" style="display: {if isset($available_pdf)}visible{else}none{/if};"><a href="?pdf" class="btn btn-info" title="Download as PDF"><i class="icon-download icon-white"></i> PDF</a></li>
                        </ul>
                    </div>
                    <div id="tinymce" style="">
                        <textarea id="editable" cols="100" rows="100">{$roadbook_content}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {include file="footer.tpl"}

        <script src="../bootstrap/js/bootstrap.min.js"></script>
        <script src="../lib/georoadbook.js" type="text/javascript"></script>
    </body>
</html>