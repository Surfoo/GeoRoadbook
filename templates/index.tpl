<!DOCTYPE html>
<html>
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>GeoRoadBook</title>
        <link rel="stylesheet" href="css/blueprint/screen.css" type="text/css" media="screen, projection" />
        <!--[if lt IE 8]><link rel="stylesheet" href="css/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
        <link rel="stylesheet" href="css/design.css" media="screen, projection" />
    </head>
    <body>
        <h1>GeoRoadBook</h1>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <fieldset>
                {if isset($errors)}
                <div class="error">
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
                <p><label for="gpx">GPX File: (Max {$max_filesize|escape})</label><br /><input type="file" name="gpx" id="gpx" /></p>
                <p><label for="locale">Language:</label><br />
                    <select name="locale" id="locale" size="2">
                        <option value="en">English</option>
                        <option value="fr">Français</option>
                    </select>
                </p>
                <p>
                <input type="checkbox" name="note" id="note" /> <label for="note">Display a cache note area</label><br />
                <input type="checkbox" name="short_desc" id="short_desc" /> <label for="short_desc">Display short description</label><br />
                <input type="checkbox" name="logs" id="logs" /> <label for="logs">Display logs</label>
                </p>
                <!--<p><label for="typog­ra­phy">Improve web typog­ra­phy (experimental):</label><input type="checkbox" name="typog­ra­phy" id="typog­ra­phy" /></p>-->
                <input type="submit" name="send" />
            </fieldset>
        </form>
        <div id="footer">
            <p><a href="https://github.com/Surfoo/georoadbook/issues?direction=desc&amp;sort=created&amp;state=open">Report Bugs / issues</a><br />
            <a href="https://github.com/Surfoo/georoadbook">Source available on github.com</a></p>
        </div>
    </body>
</html>