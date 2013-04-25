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
                <header>
                    <h1>GeoRoadBook</h1>
                    <p><small>GeoRoadbook is a web app to get your geocaching roadbook ready-to-print from your gpx file (pocket query, gsak).</small></p>
                </header>
                {if isset($deleted)}
                    <div class="alert alert-success fade in">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Your roadbook has been deleted!
                    </div>
                {/if}
                <form action="index.php" method="post" enctype="multipart/form-data" class="form-horizontal">
                    <fieldset>
                        {if isset($errors)}
                            <div class="alert alert-block">
                                <button type="button" class="close" data-dismiss="error">&times;</button>
                                <ul>
                                {foreach from=$errors item=error}
                                {if $error == "MISSING_FILE"}
                                    <li>GPX file is missing.</li>
                                {/if}
                                {if $error == "INVALID_FILE"}
                                    <li>GPX file is invalid.</li>
                                {/if}
                                {if $error == "INVALID_SCHEMA"}
                                    <li>GPX file is invalid.</li>
                                {/if}
                                {if $error == "INVALID_LOCALE"}
                                    <li>Language is invalid.</li>
                                {/if}
                                {/foreach}
                                </ul>
                            </div>
                        {/if}
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
                                <option value="en" selected="selected">English</option>
                                <option value="fr">Français</option>
                            </select></label>
                        </p>
                        <fieldset id="options">
                            <legend class="small">Options:</legend>
                            <div class="control-group">
                                <label for="note" class="checkbox inline"><input type="checkbox" name="note" id="note" /> Display a cache note area</label><br />
                                <label for="short_desc" class="checkbox inline"><input type="checkbox" name="short_desc" id="short_desc" /> Display short description</label><br />
                                <label for="hint" class="checkbox inline"><input type="checkbox" name="hint" id="hint" /> Display additionnal hint</label><br />
                                    <div id="hint_options">
                                        <label for="hint_encrypted" class="radio inline"><input type="radio" name="hint_encrypted" id="hint_encrypted" value="1" /> Encrypted</label>
                                        <label for="hint_decrypted" class="radio inline"><input type="radio" name="hint_encrypted" id="hint_decrypted" value="0" checked="checked" /> Decrypted</label>
                                    </div>
                                <label for="logs" class="checkbox inline"><input type="checkbox" name="logs" id="logs" /> Display logs</label>
                            </div>
                        </fieldset>

                        <input type="submit" name="send" value="Create your roadbook" class="btn btn-large btn-primary" />
                    </fieldset>
                </form>
            </div>
        </div>

        <footer id="footer">
          <div class="container">
            <p class="muted credit">
                <a href="https://github.com/Surfoo/georoadbook/issues?direction=desc&amp;sort=created&amp;state=open">Report Bugs / issues</a><br />
                <a href="https://github.com/Surfoo/georoadbook">Source available on github.com</a>
            </p>
          </div>
        </footer>

        <script type="text/javascript" src="lib/jquery-2.0.0.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
        <script src="bootstrap/js/bootstrap-fileupload.js"></script>
        <script type="text/javascript">
        showHintOptions();
        $('#hint').change(function() {
            showHintOptions();
        });
        function showHintOptions() {
            if($('#hint').is(':checked')) {
                $('#hint_options').show();
            }
            else {
                $('#hint_options').hide();
            }
        }

        </script>
    </body>
</html>