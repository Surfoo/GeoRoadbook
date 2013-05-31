<div id="faq" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>FAQ</h3>
    </div>
    <div class="modal-body">
        <dl>
            <dt>My language isn't in the list, how to get it?</dt>
            <dd>You can translate <a href="https://github.com/Surfoo/georoadbook/blob/master/locales/en.xml" onclick="window.open(this.href);return false;">this file</a> and send it by mail or pull request on github. I'll add it quickly then.</dd>

            <dt>Why does my roadbook no longer exist?</dt>
            <dd>All roadbooks are kept 1 month after the last modification date. You can know the date with a hover on the "Save" button, and if you save it, it'll be kept again.</dd>

            <dt>Why doesn't my GPX file work?</dt>
            <dd>Only GPX files with the version 1.0.1 are managed, if you don't know the version, <a href="http://www.geocaching.com/account/ManagePreferences.aspx" onclick="window.open(this.href);return false;">check your preferences</a>.<br />
                GPX with tracks are not supported. If your file always doesn't work, it's maybe malformed.</dd>

            <dt>Why I can't see spoilers in the roadbook?</dt>
            <dd>Unfortunately, the links to spoilers aren't included in the gpx file, maybe one day Groundspeak will fix this, who knows?<br />
                If you wants spoilers in the roadbook, you should insert them manually with the image button.</dd>

            <dt>Why is pagination missing in the table of contents?</dt>
            <dd>WebKit, the visual rendering engine used, doesn't implement this feature yet. You should make the pagination manually according to a first exportation in PDF.<br />
                You can also use the Print Preview in Chrome with the roadbook in HTML view.</dd>

            <dt>Why does my roadbook look good in the editor and not in the PDF?</dt>
            <dd>The PDF rendering is great but some HTML/CSS features aren't implemented yet.<dd>

            <dt>I have a problem with georoabook, what can I do ?</dt>
            <dd>If you find bugs, please <a href="https://github.com/Surfoo/georoadbook/issues" onclick="window.open(this.href);return false;">open a bug issue on github</a>, or <a href="#about" data-toggle="modal">contact me</a>.</dd>
        </dl>
    </div>
</div>
