<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>GeoRoadBook, create your geocaching roadbook</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="bootstrap/css/bootstrap-fileupload.min.css" rel="stylesheet" media="screen">
        <link href="css/design.css" rel="stylesheet" media="all">
    </head>
    <body>
        <div class="container">
            <div class="hero-unit">
                {include file="header.tpl"}
                
                {if isset($deleted)}
                    <div class="alert alert-success fade in">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Your roadbook has been deleted!
                    </div>
                {/if}
                <form action="#" method="post" class="form-horizontal">
                    <fieldset>
                        <div id="error" class="alert alert-block hide"></div>
                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                    <span class="fileupload-new">GPX File</span>
                                    <span class="fileupload-exists">Change</span><input type="file" name="gpx" id="gpx" />
                                </span>
                                <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
                            </div> <small>(Max {$max_filesize|escape})</small>
                        </div>
                        <p><label for="locale">Language:<br />
                            <select name="locale" id="locale" size="2">
                                <option value="en"{if $language == "en"} selected="selected"{/if}>English</option>
                                <option value="fr"{if $language == "fr"} selected="selected"{/if}>Français</option>
                            </select></label>
                        </p>
                        <fieldset id="options">
                            <legend class="small">Options:</legend>
                            <div class="control-group">
                                <label for="tidy" class="checkbox inline"><input type="checkbox" name="tidy" id="tidy" value="1" /> Cleanup & repair HTML</label><br />
                                <label for="toc" class="checkbox inline"><input type="checkbox" name="toc" id="toc" value="1" /> Table of content</label><br />
                                <label for="note" class="checkbox inline"><input type="checkbox" name="note" id="note" value="1" /> Display a cache note area</label><br />
                                <label for="short_desc" class="checkbox inline"><input type="checkbox" name="short_desc" id="short_desc" value="1" /> Display short description</label><br />
                                <label for="hint" class="checkbox inline"><input type="checkbox" name="hint" id="hint" value="1" /> Display additionnal hints</label><br />
                                    <div id="hint_options">
                                        <label for="hint_encrypted" class="radio inline"><input type="radio" name="hint_encrypted" id="hint_encrypted" value="1" /> Encrypted</label>
                                        <label for="hint_decrypted" class="radio inline"><input type="radio" name="hint_encrypted" id="hint_decrypted" value="0" checked="checked" /> Decrypted</label>
                                    </div>
                                <label for="logs" class="checkbox inline"><input type="checkbox" name="logs" id="logs" value="1" /> Display logs</label>
                            </div>
                        </fieldset>

                        <input type="submit" id="create" name="send" value="Create your roadbook" class="btn btn-large btn-primary" />
                    </fieldset>
                </form>
            </div>
        </div>

        {include file="footer.tpl"}

        <script src="js/jquery-2.0.0.min.js" type="text/javascript"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <script src="bootstrap/js/bootstrap-fileupload.js"></script>
        <script src="js/upload.js" type="text/javascript"></script>
    </body>
</html>
