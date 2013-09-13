<!DOCTYPE html>
<html lang="en">
    <head>
        <meta content="text/html; charset=UTF-8" http-equiv="content-type"/>
        <title>GeoRoadbook, create your geocaching roadbook</title>
        <meta name="description" content="GeoRoadbook is a free and open source web app to create your geocaching roadbook ready-to-print from your gpx file (Pocket queries, GSAK, GCTour, etc.)">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" href="/design/icon-roadbook.png" />
        <link rel="stylesheet" href="/bootstrap/css/bootstrap.min.css" media="all" />
        <link rel="stylesheet" href="/bootstrap/css/bootstrap-fileupload.min.css" media="all" />
        <link rel="stylesheet" href="/design/design.css?{{ constant('SUFFIX_CSS_JS') }}" media="all" />
    </head>
    <body>
        <div class="container">
            <div class="hero-unit">
                {% include 'header.tpl' %}
                
                {% if deleted is defined %}
                    <div class="alert alert-success fade in">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Your roadbook has been deleted!
                    </div>
                {% endif %}
                <form action="#" method="post" class="form-horizontal">
                    <fieldset>
                        <div id="error" class="alert alert-block hide"></div>
                        <div class="fileupload fileupload-new" data-provides="fileupload">
                            <div class="input-append">
                                <div class="uneditable-input span3">
                                    <i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"></span>
                                </div>
                                <span class="btn btn-file">
                                    <span class="fileupload-new">Browse to GPX File</span>
                                    <span class="fileupload-exists">Change</span><input type="file" name="gpx" id="gpx" />
                                </span>
                                <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
                            </div> <i class="icon-question-sign option-help" data-toggle="tooltip" title="Choose a GPX file and upload it. 8Mo maximum."></i>
                        </div>
                        <p><label for="locale">Roadbook language:<br />
                            <select name="locale" id="locale">
                                <option value=""></option>
                                {% for code, name in locales %}
                                <option value="{{ code|e }}"{% if language == code %} selected="selected"{% endif %}>{{ name|e }}</option>
                                {% endfor %}
                            </select> <i class="icon-question-sign option-help" data-toggle="tooltip" title="Choose the language of your roadbook."></i></label>
                        </p>
                        <fieldset id="options">
                            <legend class="small">Options:</legend>

                            <div class="row">
                                <div class="span5">
                                    <div class="control-group">
                                        <p class="small">General:</p>

                                        <label for="sort" class="checkbox inline"><input type="checkbox" name="sort" id="sort" value="1" /> Sort caches by</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Choose how to sort caches. Sorted according to your GPX file by default."></i><br />
                                        <div id="sort_options" class="hide">
                                            <label for="sort1" class="radio inline"><input type="radio" name="sort_by" id="sort1" value="name" /> Name</label>
                                            <label for="sort2" class="radio inline"><input type="radio" name="sort_by" id="sort2" value="owner" /> Owner</label>
                                            <label for="sort3" class="radio inline"><input type="radio" name="sort_by" id="sort3" value="difficulty" /> Difficulty</label>
                                            <label for="sort4" class="radio inline"><input type="radio" name="sort_by" id="sort4" value="terrain" /> Terrain</label>
                                        </div>

                                        <label for="toc" class="checkbox inline"><input type="checkbox" name="toc" id="toc" value="1" /> Table of contents</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Display the summary of caches, in 6 columns : Number, Type, Name, Found, Didn't Find and Page."></i><br />
                                    </div>
                                </div>
                                <div class="span5">
                                    <div class="control-group">
                                        <p class="small">Geocaches:</p>

                                        <label for="note" class="checkbox inline"><input type="checkbox" name="note" id="note" value="1" /> Cache note area</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Display a writing area for notes, calculations..."></i><br />

                                        <label for="images" class="checkbox inline"><input type="checkbox" name="images" id="images" value="1" /> Remove images</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Remove images from the short and long description."></i><br />

                                        <label for="short_desc" class="checkbox inline"><input type="checkbox" name="short_desc" id="short_desc" value="1" /> Short description</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Display the short description from caches."></i><br />

                                        <label for="hint" class="checkbox inline"><input type="checkbox" name="hint" id="hint" value="1" /> Additionnal hints</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Display the cache hints. Decrypted by default."></i><br />

                                        <div id="hint_options" class="hide">
                                            <label for="hint_decrypted" class="radio inline"><input type="radio" name="hint_encrypted" id="hint_decrypted" value="0" checked="checked" /> Decrypted</label>
                                            <label for="hint_encrypted" class="radio inline"><input type="radio" name="hint_encrypted" id="hint_encrypted" value="1" /> Encrypted</label>
                                        </div>

                                        <label for="waypoints" class="checkbox inline"><input type="checkbox" name="waypoints" id="waypoints" value="1" /> Waypoints</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Display additionnals waypoints like parking, stage, questions, etc."></i><br />

                                        <label for="logs" class="checkbox inline"><input type="checkbox" name="logs" id="logs" value="1" /> Logs</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Display all logs from your GPX file."></i><br />

                                        <label for="pagebreak" class="checkbox inline"><input type="checkbox" name="pagebreak" id="pagebreak" value="1" /> Page break</label>
                                        <i class="icon-question-sign option-help" data-toggle="tooltip" title="Insert a page break between each caches."></i>
                                    </div>
                                </div>
                            </div>
                        </fieldset>

                        <input type="submit" id="create" name="send" value="Create your roadbook" data-loading-text="Creating..." class="btn btn-large btn-primary pull-right" />
                    </fieldset>
                </form>
                {% include '_faq.tpl' %}
                {% include '_about.tpl' %}
                {% include '_donate.tpl' %}
            </div>
        </div>

        {% include 'footer.tpl' %}

        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/{{ constant('JQUERY_VERSION') }}/jquery.min.js"></script>
        <script type="text/javascript" src="//netdna.bootstrapcdn.com/twitter-bootstrap/{{ constant('BOOTSTRAP_VERSION') }}/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="/bootstrap/js/bootstrap-fileupload.js"></script>
        <script type="text/javascript" src="/js/upload.min.js?{{ constant('SUFFIX_CSS_JS') }}"></script>
    </body>
</html>
