<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>GeoRoadBook, create your geocaching roadbook</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="/css/icon-roadbook.png" />
        <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" href="/css/design.css" media="all">
        <script type="text/javascript" src="/js/jquery-2.0.0.min.js"></script>
        <!--<script type="text/javascript" src="/js/tinymce4/tinymce.min.js"></script>-->
        <script type="text/javascript" src="/js/tinymce358/tiny_mce.js"></script>
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
                        <div class="btn-group">
                            <button type="submit" id="save" class="btn btn-primary" title="{$last_modification|escape}">Save</button>
                            <a href="#ui_export" role="button" class="btn btn-primary" data-toggle="modal">Export</a>
                        </div>
                        <div class="btn-group">
                            <button class="btn">Download as</button>
                            <button class="btn dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                            </button>
                            <ul id="download" class="dropdown-menu">
                                <li><a href="?raw" title="View only as HTML page"><img src="/css/icon-html.png" alt="" /> HTML</a></li>
                                {if isset($available_zip)}<li><a href="?zip" title="Download a zip archive (HTML and images)"><img src="/css/icon-zip.png" alt="" /> ZIP</a></li>{/if}
                                <li id="dl_pdf" style="display: {if isset($available_pdf)}visible{else}none{/if};"><a href="?pdf" title="Download as PDF"><img src="/css/icon-pdf.png" alt="" /> PDF</a></li>
                            </ul>
                        </div>
                        {include file="export.tpl"}
                    </div>
                    <div id="tinymce">
                        <textarea id="editable" cols="100" rows="100">{$roadbook_content}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {include file="footer.tpl"}

        <script src="/bootstrap/js/bootstrap.min.js"></script>
        <script src="/js/georoadbook.js" type="text/javascript"></script>
    </body>
</html>
