<!-- Modal -->
<div id="ui_export" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="ui_export" aria-hidden="true">
    <div class="modal-body">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
        <div class="tabbable"> <!-- Only required for left/right tabs -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#tab1" data-toggle="tab">Options</a></li>
                <li><a href="#tab2" data-toggle="tab">Header & Footer</a></li>
            </ul>
            <form class="form-horizontal">
                <div class="tab-content">
                    <div class="tab-pane active" id="tab1">

                        <div class="control-group">
                            <label class="control-label" for="page_size">Page size:</label>
                            <div class="controls">
                                <select name="page_size" id="page_size">
                                    <option value="A4">A4</option>
                                    <option value="A5">A5</option>
                                </select>
                            </div>
                            <label class="control-label" for="orientation">Orientation</label>
                            <div class="controls">
                                <select name="orientation" id="orientation">
                                    <option value="portrait">Portrait</option>
                                    <option value="landscape">Landscape</option>
                                </select>
                            </div>
                        </div>

                        <fieldset>
                            <legend>Margins</legend>
                            <div class="control-group">
                                <label class="control-label" for="margin_left">Left</label>
                                <div class="controls">
                                    <input name="margin_left" type="number" size="2" maxlength="2" id="margin_left" value="10" style="width:40px;" /> <small>mm</small>
                                </div>

                                <label class="control-label" for="margin_right">Right</label>
                                <div class="controls">
                                    <input name="margin_right" type="number" size="2" maxlength="2" id="margin_right" value="10" style="width:40px;" /> <small>mm</small>
                                </div>

                                <label class="control-label" for="margin_top">Top</label>
                                <div class="controls">
                                    <input name="margin_top" type="number" size="2" maxlength="2" id="margin_top" value="10" style="width:40px;" /> <small>mm</small>
                                </div>

                                <label class="control-label" for="margin_bottom">Bottom</label>
                                <div class="controls">
                                    <input name="margin_bottom" type="number" size="2" maxlength="2" id="margin_bottom" value="10" style="width:40px;" /> <small>mm</small>
                                </div>

                            </div>
                        </fieldset>
                    </div>
                    <div class="tab-pane" id="tab2">
                        <fieldset>
                            <legend>Header Text</legend>
                                <label class="control-label" for="header_left">Left</label>
                                <div class="controls">
                                    <input name="header_left" type="text" id="header_left" />
                                </div>

                                <label class="control-label" for="header_center">Centered</label>
                                <div class="controls">
                                    <input name="header_center" type="text" id="header_center" />
                                </div>

                                <label class="control-label" for="header_right">Right</label>
                                <div class="controls">
                                    <input name="header_right" type="text" id="header_right" />
                                </div>
                        </fieldset>
                        <fieldset>
                            <legend>Footer Text</legend>
                                <label class="control-label" for="footer_left">Left</label>
                                <div class="controls">
                                    <input name="footer_left" type="text" id="footer_left" />
                                </div>

                                <label class="control-label" for="footer_center">Centered</label>
                                <div class="controls">
                                    <input name="footer_center" type="text" id="footer_center" />
                                </div>

                                <label class="control-label" for="footer_right">Right</label>
                                <div class="controls">
                                    <input name="footer_right" type="text" id="footer_right" />
                                </div>
                        </fieldset>
                        <small>
                            <p><strong>Variables:</strong> [page], [topage]</p>
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        <button id="apply" class="btn btn-primary">Apply</button>
        <button id="export" class="btn btn-primary">Export</button>
    </div>
</div>

<div id="ui_exported" class="modal hide fade" tabindex="-2" role="dialog" aria-labelledby="ui_exported" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Exported successfully!</h3>
    </div>
    <div class="modal-body">
        <p id="download_link"></p>
        <small>You can also download your roadbook later with the "Download as" button.</small>
    </div>
    <div class="modal-footer">
        <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    </div>
</div>
