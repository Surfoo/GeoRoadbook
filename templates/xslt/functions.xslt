<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<!-- from http://kodlib.info/xsltckbk2-CHP-4-SECT-10.shtml --> 
<xsl:template name="format-date">
    <xsl:param name="year" />
    <xsl:param name="month" />
    <xsl:param name="day" />
    <xsl:param name="format" select="'%Y/%m/%d'" />
   
    <xsl:choose>
        <xsl:when test="contains($format, '%')">
            <xsl:value-of select="substring-before($format, '%')"/>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$format"/>
        </xsl:otherwise>
    </xsl:choose>
   
    <xsl:variable name="code" select="substring(substring-after($format, '%'), 1, 1)"/>
    <xsl:choose>

        <!-- Day of month as decimal number (01 - 31) -->
        <xsl:when test="$code='d'">
            <xsl:value-of select="format-number($day,'00')"/>
        </xsl:when>
       
        <!-- Month as decimal number (01 - 12) -->
        <xsl:when test="$code='m'">
            <xsl:value-of select="format-number($month,'00')"/>
        </xsl:when>

        <!-- Year without century, as decimal number (00 - 99) -->
        <xsl:when test="$code='y'">
            <xsl:value-of select="format-number($year mod 100,'00')"/>  
        </xsl:when>
       
        <!-- Year with century, as decimal number -->
        <xsl:when test="$code='Y'">
            <xsl:value-of select="format-number($year,'0000')"/>
        </xsl:when>

        <!-- Percent sign -->
        <xsl:when test="$code='%'">
            <xsl:text>%</xsl:text>
        </xsl:when>
   
    </xsl:choose>
   
    <xsl:variable name="remainder" 
                  select="substring(substring-after($format, '%'), 2)"/>
   
    <xsl:if test="$remainder">
      <xsl:call-template name="format-date">
        <xsl:with-param name="year" select="$year"/>
        <xsl:with-param name="month" select="$month"/>
        <xsl:with-param name="day" select="$day"/>
        <xsl:with-param name="format" select="$remainder"/>
      </xsl:call-template>
    </xsl:if>
   
</xsl:template>

<xsl:template name="PreserveLineBreaks">
    <xsl:param name="text"/>
    <xsl:choose>
        <xsl:when test="contains($text,'&#xA;')">
            <xsl:value-of select="substring-before($text,'&#xA;')"/>
            <br/>
            <xsl:call-template name="PreserveLineBreaks">
                <xsl:with-param name="text">
                    <xsl:value-of select="substring-after($text,'&#xA;')"/>
                </xsl:with-param>
            </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="$text"/>
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>