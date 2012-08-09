<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">

    <!-- Hint -->
    <xsl:template match="grdspk:cache/grdspk:encoded_hints">
        <xsl:if test="string(normalize-space(.))">
            <h3><xsl:value-of select="$locale/text[@id='hint']" /></h3>
            <p>
                <xsl:call-template name="PreserveLineBreaks">
                    <xsl:with-param name="text" select="."/>
                </xsl:call-template>
            </p>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>