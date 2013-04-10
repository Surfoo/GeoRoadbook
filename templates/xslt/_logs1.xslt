<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">
    
    <!--logs-->
    <xsl:template match="grdspk:cache/grdspk:logs/grdspk:log">
        <xsl:variable name="year" select="substring(grdspk:date,1,4)" />
        <xsl:variable name="month-temp" select="substring-after(grdspk:date,'-')" />
        <xsl:variable name="month" select="substring-before($month-temp,'-')" />
        <xsl:variable name="day-temp" select="substring-after($month-temp,'-')" />
        <xsl:variable name="day" select="substring($day-temp,1,2)" />
        <tr>
            <td class="finder_name" valign="middle">
                <xsl:choose>
                    <xsl:when test="grdspk:type = 'Found it'">
                        <img src="../img/log/icon_smile.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Needs Maintenance'">
                        <img src="../img/log/icon_needsmaint.gif" alt="" />
                    </xsl:when>
                    <xsl:when test='grdspk:type = "Didn&apos;t find it"'>
                        <img src="../img/log/icon_sad.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Owner Maintenance'">
                        <img src="../img/log/icon_maint.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Enable Listing'">
                        <img src="../img/log/icon_enabled.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Temporarily Disable Listing'">
                        <img src="../img/log/icon_disabled.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Webcam Photo Taken'">
                        <img src="../img/log/icon_camera.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Update Coordinates'">
                        <img src="../img/log/coord_update.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Publish Listing'">
                        <img src="../img/log/icon_greenlight.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Archive'">
                        <img src="../img/log/traffic_cone.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Announcement'">
                        <img src="../img/log/icon_announcement.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Need Archived'">
                        <img src="../img/log/icon_remove.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Will Attend'">
                        <img src="../img/log/icon_rsvp.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Attended'">
                        <img src="../img/log/icon_attended.gif" alt="" />
                    </xsl:when>
                    <xsl:when test="grdspk:type = 'Write note'">
                        <img src="../img/log/icon_note.gif" alt="" />
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="grdspk:type" />
                    </xsl:otherwise>
                </xsl:choose>
                &#160;<xsl:value-of select="grdspk:finder" /> 
            </td>
            <td width="50mm" align="right">
                <xsl:value-of select="concat($day, '/', $month, '/', $year)" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <xsl:call-template name="PreserveLineBreaks">
                    <xsl:with-param name="text" select="grdspk:text"/>
                </xsl:call-template>
                <br />
            </td>
        </tr>
    </xsl:template>
</xsl:stylesheet>