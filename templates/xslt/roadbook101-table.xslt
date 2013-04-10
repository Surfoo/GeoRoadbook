<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">

<!--<xsl:import href="main.xslt" />-->
<xsl:import href="functions.xslt" />
<xsl:import href="_attributes101.xslt" />
<xsl:import href="_hint.xslt" />
<xsl:import href="_logs101.xslt" />

<xsl:output method="html"
  indent="yes"
  omit-xml-declaration="yes"
  encoding="utf-8"
  doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
  doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd" />

<xsl:variable name="locale" select="document($locale_filename)/i18n" />

<xsl:template match="/">
  <html>
    <head>
      <meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
      <title>GeoRoadbook</title>
      <link type="text/css" rel="stylesheet" href="../css/mycontent.css?2" media="all" />
    </head>
    <body>

    <xsl:if test="$zip_archive">
        <p id="zip_archive"><a href="{$zip_filename}"><xsl:value-of select="$locale/text[@id='zip_archive']" /></a></p>
    </xsl:if>

    <xsl:for-each select="gpx:gpx/gpx:wpt">
        <xsl:if test="grdspk:cache">
        <div class="cache">
        <table class="cacheInfos" cellpadding="0" cellspacing="0" width="100%" border="0">
          <tr>
            <td colspan="2" width="400">
              <h2 class="cacheTitle"><span>
              <xsl:choose>
                <xsl:when test="grdspk:cache/grdspk:type = 'Traditional Cache'">
                  <img src="../img/caches/32x32/traditional.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Multi-cache'">
                  <img src="../img/caches/32x32/multi.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Unknown Cache'">
                  <img src="../img/caches/32x32/mystery.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Event Cache'">
                  <img src="../img/caches/32x32/event.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Webcam Cache'">
                  <img src="../img/caches/32x32/webcam.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Wherigo Cache'">
                  <img src="../img/caches/32x32/wherigo.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Earthcache'">
                  <img src="../img/caches/32x32/earthcache.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Virtual Cache'">
                  <img src="../img/caches/32x32/virtual.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Letterbox Hybrid'">
                  <img src="../img/caches/32x32/letterbox.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Cache In Trash Out Event'">
                  <img src="../img/caches/32x32/cito.gif" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:type = 'Mega-Event Cache'">
                  <img src="../img/caches/32x32/megaevent.gif" alt="" />
                </xsl:when>
                <xsl:otherwise>
                  <img src="../img/not_chosen.gif" alt="" />
                </xsl:otherwise>
              </xsl:choose>
              </span>
              <xsl:value-of select="grdspk:cache/grdspk:name"/></h2>
            </td>
            <td align="right">
              <h2><xsl:value-of select="gpx:name"/></h2>
            </td>
          </tr>
          <tr>
            <td colspan="2">
                <!-- Latitude conversion -->
              <xsl:variable name="latitude_text">
                  <xsl:choose>
                      <xsl:when test="number(substring-before(@lat, '.' )) &lt; 0"> S </xsl:when>
                      <xsl:when test="number(substring-before(@lat, '.' )) &gt; 0"> N </xsl:when>
                  </xsl:choose>
              </xsl:variable>
              <xsl:variable name="latitude_degrees">
                  <xsl:choose>
                      <xsl:when test="number(substring-before(@lat, '.' )) &lt; 0"><xsl:value-of select="substring-before(@lat, '.' ) * -1" /></xsl:when>
                      <xsl:when test="number(substring-before(@lat, '.' )) &gt; 0"><xsl:value-of select="substring-before(@lat, '.' )" /></xsl:when>
                  </xsl:choose>
              </xsl:variable>

              <!-- Longitude conversion -->
              <xsl:variable name="longitude_text">
                  <xsl:choose>
                      <xsl:when test="number(substring-before(@lon, '.' )) &lt;= 0"> W </xsl:when>
                      <xsl:when test="number(substring-before(@lon, '.' )) &gt; 0"> E </xsl:when>
                  </xsl:choose>
              </xsl:variable>
              <xsl:variable name="longitude_degrees">
                  <xsl:choose>
                      <xsl:when test="number(substring-before(@lon, '.' )) &lt;= 0"><xsl:value-of select="substring-before(@lon, '.' ) * -1" /></xsl:when>
                      <xsl:when test="number(substring-before(@lon, '.' )) &gt; 0"><xsl:value-of select="substring-before(@lon, '.' )" /></xsl:when>
                  </xsl:choose>
              </xsl:variable>
                <xsl:variable name="lat" select="concat($latitude_text, format-number($latitude_degrees, '00'),'° ', substring(format-number(number(concat('.', substring-after(@lat, '.' )) * 60), '00.000'), 0, 7))" />
                <xsl:variable name="lon" select="concat($longitude_text, format-number($longitude_degrees, '000'),'° ', substring(format-number(number(concat('.', substring-after(@lon, '.' )) * 60), '00.000'), 0, 7))" />
                  <span><strong><xsl:value-of select="$lat"/>&#160;<xsl:value-of select="$lon"/></strong></span>
            </td>
            <td align="right">
              <xsl:variable name="hidden_date">
                <xsl:variable name="year" select="substring(gpx:time,1,4)" />
                <xsl:variable name="month-temp" select="substring-after(gpx:time,'-')" />
                <xsl:variable name="month" select="substring-before($month-temp,'-')" />
                <xsl:variable name="day-temp" select="substring-after($month-temp,'-')" />
                <xsl:variable name="day" select="substring($day-temp,1,2)" />
                <xsl:call-template name="format-date">
                    <xsl:with-param name="year"  select="$year"></xsl:with-param>
                    <xsl:with-param name="month" select="$month"></xsl:with-param>
                    <xsl:with-param name="day"   select="$day"></xsl:with-param>
                    <xsl:with-param name="format" select="$locale/format[@id='date']"></xsl:with-param>
                </xsl:call-template>
              </xsl:variable>

              <span>
                <xsl:if test="string(normalize-space(grdspk:cache/grdspk:placed_by))">
                  <xsl:value-of select="$locale/text[@id='by']" />&#160;<strong><xsl:value-of select="grdspk:cache/grdspk:placed_by" /></strong>,
                </xsl:if>
                <xsl:value-of select="$locale/text[@id='hidden']" /><strong>&#160;<xsl:value-of select="$hidden_date" /></strong>
              </span>
            </td>
          </tr>
          <tr>
            <td width="80mm">
              <xsl:value-of select="$locale/text[@id='difficulty']" />
            </td>
            <td>
              <span>
               <xsl:choose>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '1'">
                  <img src="../img/cotation/stars1.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '1.5'">
                  <img src="../img/cotation/stars1_5.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '2'">
                  <img src="../img/cotation/stars2.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '2.5'">
                  <img src="../img/cotation/stars2_5.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '3'">
                  <img src="../img/cotation/stars3.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '3.5'">
                  <img src="../img/cotation/stars3_5.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '4'">
                  <img src="../img/cotation/stars4.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '4.5'">
                  <img src="../img/cotation/stars4_5.png" alt="" />
                </xsl:when>
                <xsl:when test="grdspk:cache/grdspk:difficulty = '5'">
                  <img src="../img/cotation/stars5.png" alt="" />
                </xsl:when>
              </xsl:choose>
              </span>
            </td>
            <td align="right">
              <xsl:value-of select="$locale/text[@id='size']" />&#160;
                        <xsl:choose>
                          <xsl:when test="grdspk:cache/grdspk:container = 'Micro'">
                            <img src="../img/container/micro.gif" alt="" />
                          </xsl:when>
                          <xsl:when test="grdspk:cache/grdspk:container = 'Small'">
                            <img src="../img/container/small.gif" alt="" />
                          </xsl:when>
                          <xsl:when test="grdspk:cache/grdspk:container = 'Regular'">
                            <img src="../img/container/regular.gif" alt="" />
                          </xsl:when>
                          <xsl:when test="grdspk:cache/grdspk:container = 'Large'">
                            <img src="../img/container/large.gif" alt="" />
                          </xsl:when>
                          <xsl:when test="grdspk:cache/grdspk:container = 'Other'">
                            <img src="../img/container/other.gif" alt="" />
                          </xsl:when>
                          <xsl:when test="grdspk:cache/grdspk:container = 'Not chosen' or grdspk:cache/grdspk:container = 'not_chosen'">
                            <img src="../img/container/not_chosen.gif" alt="" />
                          </xsl:when>
                        </xsl:choose>
                        &#160;(<xsl:value-of select="grdspk:cache/grdspk:container" />)
            </td>
          </tr>
          <tr>
            <td width="80mm">
              <xsl:value-of select="$locale/text[@id='terrain']" />
            </td>
            <td>
              <span>
                <xsl:choose>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '1'">
                    <img src="../img/cotation/stars1.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '1.5'">
                    <img src="../img/cotation/stars1_5.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '2'">
                    <img src="../img/cotation/stars2.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '2.5'">
                    <img src="../img/cotation/stars2_5.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '3'">
                    <img src="../img/cotation/stars3.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '3.5'">
                    <img src="../img/cotation/stars3_5.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '4'">
                    <img src="../img/cotation/stars4.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '4.5'">
                    <img src="../img/cotation/stars4_5.png" alt="" />
                  </xsl:when>
                  <xsl:when test="grdspk:cache/grdspk:terrain = '5'">
                    <img src="../img/cotation/stars5.png" alt="" />
                  </xsl:when>
                </xsl:choose>
              </span>
            </td>
            <td align="right">
              <!--<xsl:if test="string(normalize-space(grdspk:cache/grdspk:state))">
                <xsl:value-of select="$locale/text[@id='location']" />&#160;<xsl:value-of select="grdspk:cache/grdspk:state" />, <xsl:value-of select="grdspk:cache/grdspk:country" />
              </xsl:if>-->
            </td>
          </tr>
          <xsl:if test='grdspk:cache/grdspk:attributes/grdspk:attribute'>
            <tr>
                <td colspan="3">
                  <ul class="attributes">
                    <xsl:apply-templates select="grdspk:cache/grdspk:attributes/grdspk:attribute" />
                  </ul>
                </td>
            </tr>
          </xsl:if>
        </table>

            <!-- short_description -->
            <div class="short_description">
                <xsl:choose>
                    <xsl:when test="grdspk:cache/grdspk:short_description/@html = 'True'">
                        <xsl:value-of select="grdspk:cache/grdspk:short_description" disable-output-escaping="yes" />
                    </xsl:when>
                    <xsl:when test="grdspk:cache/grdspk:short_description/@html = 'False'">
                        <xsl:call-template name="PreserveLineBreaks">
                            <xsl:with-param name="text" select="grdspk:cache/grdspk:short_description"/>
                        </xsl:call-template>
                    </xsl:when>
                </xsl:choose>
            </div>

            <!-- long_description -->
            <div class="long_description">
                <xsl:choose>
                    <xsl:when test="grdspk:cache/grdspk:long_description/@html = 'True'">
                        <xsl:value-of select="grdspk:cache/grdspk:long_description" disable-output-escaping="yes" />
                    </xsl:when>
                    <xsl:when test="grdspk:cache/grdspk:long_description/@html = 'False'">
                        <xsl:call-template name="PreserveLineBreaks">
                            <xsl:with-param name="text" select="grdspk:cache/grdspk:long_description"/>
                        </xsl:call-template>
                    </xsl:when>
                </xsl:choose>
            </div>

            <xsl:apply-templates select="grdspk:cache/grdspk:encoded_hints" />

            <!-- Logs -->
            <xsl:if test='$display_logs and grdspk:cache/grdspk:logs/grdspk:log'>
                <div class="cacheLogs">
                  <h2><xsl:value-of select="$locale/text[@id='logs']" /></h2>
                  <table cellspacing="0" cellpadding="0" width="100%">
                      <xsl:apply-templates select="grdspk:cache/grdspk:logs/grdspk:log" />
                  </table>
                </div>
            </xsl:if>
          </div>
          <div class="pagebreak"><xsl:text disable-output-escaping="yes"><![CDATA[<!-- pagebreak -->]]></xsl:text></div>
        </xsl:if>
        </xsl:for-each>
  </body>
  </html>
</xsl:template>
</xsl:stylesheet>