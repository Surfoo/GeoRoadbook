<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">

    <!-- Hint -->
    <xsl:template match="grdspk:cache/grdspk:encoded_hints">
        <xsl:if test="string(normalize-space(.))">
            <div class="cacheHint">
                <p><xsl:value-of select="$locale/text[@id='hint']" /></p>
                <p class="cacheHintContent">
                    <xsl:call-template name="PreserveLineBreaks">
                        <xsl:with-param name="text" select="."/>
                    </xsl:call-template>
                </p>
            </div>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>