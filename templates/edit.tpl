<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>GeoRoadbook, create your geocaching roadbook</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="/design/icon-roadbook.png" />
        <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" href="/design/design.css" media="all">
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/{$jquery_version}/jquery.min.js"></script>
        {*<script type="text/javascript" src="/js/tinymce4/tinymce.min.js"></script>*}
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
                    <div id="actions">
                        <div class="pull-right">
                            <a href="#help" data-toggle="modal"><img src="/design/circle_question_mark.png" alt="Help" title="Need help?" /></a>
                        </div>

                        <div class="btn-group">
                            <button type="submit" id="save" class="btn btn-primary" title="{$last_modification|escape}">Save</button>
                            <a href="#ui_export" id="btn_export" role="button" class="btn btn-primary" data-toggle="modal" title="Export as PDF file">Export as PDF</a>
                        </div>

                        <div class="btn-group">
                            <button class="btn btn-primary" title="Download your roadbook"><i class="icon-download icon-white"></i> Download as</button>
                            <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                <span class="caret"></span>
                            </button>
                            <ul id="download" class="dropdown-menu">
                                <li><a href="?raw" title="View only as HTML page"><img src="/design/icon-html.png" alt="" /> HTML</a></li>
                                {if isset($available_zip)}<li><a href="?zip" title="Download a zip archive (HTML and images)"><img src="/design/icon-zip.png" alt="" /> ZIP</a></li>{/if}
                                <li id="dl_pdf" style="display: {if isset($available_pdf)}visible{else}none{/if};"><a href="?pdf" title="Download as PDF"><img src="/design/icon-pdf.png" alt="" /> PDF</a></li>
                            </ul>
                        </div>

                        <div class="btn-group" title="Delete your roadbook">
                            <button type="submit" id="delete" class="btn btn-warning"><i class="icon-trash icon-white"></i> Delete</button>
                        </div>
                        {include file="export.tpl"}
                    </div>
                    <div id="tinymce">
                        <textarea id="editable" cols="100" rows="100">{$roadbook_content}</textarea>
                    </div>
                </div>
                {include file="_faq.tpl"}
                {include file="_about.tpl"}
                {include file="_donate.tpl"}
                {include file="_help.tpl"}
            </div>
        </div>

        {include file="footer.tpl"}

        <script type="text/javascript" src="//netdna.bootstrapcdn.com/twitter-bootstrap/{$bootstrap_version}/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/js/georoadbook.min.js?20130531"></script>
    </body>
</html>
