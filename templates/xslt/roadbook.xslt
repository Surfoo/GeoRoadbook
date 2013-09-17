<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:gpx="http://www.topografix.com/GPX/1/0"
  xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">

  <xsl:import href="functions.xslt" />
  <xsl:import href="_attributes.xslt" />
  <xsl:import href="_hint.xslt" />
  <xsl:import href="_logs.xslt" />
  <xsl:strip-space elements="*" />

  <xsl:variable name="locale" select="document($locale_filename)/i18n" />

  <xsl:output
     method="xml"
     doctype-system="about:legacy-compat"
     omit-xml-declaration="no"
     encoding="UTF-8"
     indent="yes" />

    <xsl:template match="/">
      <html>
        <head>
          <meta charset="utf-8" />
        </head>
      <body>

        <xsl:for-each select="gpx:gpx/gpx:wpt">
          <xsl:sort select="translate(grdspk:cache/grdspk:*[local-name()=$sort_by], 'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ')"/>
            <xsl:choose>
              <xsl:when test="grdspk:cache">

              <!-- break page or separator, only after the second geocache -->
              <xsl:if test="(position()) > 1">
                <xsl:choose>
                    <xsl:when test='$pagebreak'>
                      <p class="pagebreak"><xsl:text disable-output-escaping="yes"><![CDATA[<!-- pagebreak -->]]></xsl:text></p>
                    </xsl:when>
                    <xsl:otherwise>
                      <hr class="separator" />
                    </xsl:otherwise>
                </xsl:choose>
              </xsl:if>

              <div class="cache">

                <!-- variables -->
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
                <!-- End variables -->

                <div class="containers">
                  <div class="container1">
                      <h1 class="cacheTitle">
                          <xsl:choose>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Traditional Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/traditional.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Multi-cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/multi.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Unknown Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/mystery.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Event Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/event.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Webcam Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/webcam.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Wherigo Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/wherigo.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Earthcache'">
                                  <img src="../img/caches/{$icon_cache_dir}/earthcache.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Virtual Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/virtual.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Letterbox Hybrid'">
                                  <img src="../img/caches/{$icon_cache_dir}/letterbox.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Cache In Trash Out Event'">
                                  <img src="../img/caches/{$icon_cache_dir}/cito.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Mega-Event Cache'">
                                  <img src="../img/caches/{$icon_cache_dir}/megaevent.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Waymark'">
                                  <img src="../img/caches/{$icon_cache_dir}/waymark.gif" alt="" />
                              </xsl:when>
                              <xsl:when test="grdspk:cache/grdspk:type = 'Benchmark'">
                                  <img src="../img/caches/{$icon_cache_dir}/benchmark.gif" alt="" />
                              </xsl:when>
                              <xsl:otherwise>
                                  <img src="../img/caches/{$icon_cache_dir}/unknown.gif" alt="" />
                              </xsl:otherwise>
                          </xsl:choose>
                      <xsl:value-of select="grdspk:cache/grdspk:name"/></h1>

                      <p><strong><xsl:value-of select="$lat"/>&#160;<xsl:value-of select="$lon"/></strong></p>

                      <p><xsl:value-of select="$locale/text[@id='difficulty']" />
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
                          &#160;<xsl:value-of select="$locale/text[@id='terrain']" />
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
                      </p>
                </div>


                <div class="container2">
                  <p class="cacheGCode"><xsl:value-of select="gpx:name"/></p>
                  <p>
                      <xsl:if test="string(normalize-space(grdspk:cache/grdspk:placed_by))">
                          <xsl:value-of select="$locale/text[@id='by']" />&#160;<strong><xsl:value-of select="grdspk:cache/grdspk:placed_by" /></strong>,
                      </xsl:if>
                      <xsl:value-of select="$locale/text[@id='hidden']" /><strong>&#160;<xsl:value-of select="$hidden_date" /></strong>
                  </p>
                  <p>

                    <xsl:variable name="container">
                      <xsl:call-template name="ToLower">
                        <xsl:with-param name="inputString" select="grdspk:cache/grdspk:container"></xsl:with-param>
                      </xsl:call-template>
                    </xsl:variable>

                    <xsl:if test="$container != '' and $container != 'unknown'">
                      <xsl:value-of select="$locale/text[@id='size']" />&#160;
                      <xsl:choose>
                        <xsl:when test="$container = 'micro'">
                          <img src="../img/container/micro.gif" alt="" />&#160;(<xsl:value-of select="$locale/text[@id='micro']" />)
                        </xsl:when>
                        <xsl:when test="$container = 'small'">
                          <img src="../img/container/small.gif" alt="" />&#160;(<xsl:value-of select="$locale/text[@id='small']" />)
                        </xsl:when>
                        <xsl:when test="$container = 'regular'">
                          <img src="../img/container/regular.gif" alt="" />&#160;(<xsl:value-of select="$locale/text[@id='regular']" />)
                        </xsl:when>
                        <xsl:when test="$container = 'large'">
                          <img src="../img/container/large.gif" alt="" />&#160;(<xsl:value-of select="$locale/text[@id='large']" />)
                        </xsl:when>
                        <xsl:when test="$container = 'other'">
                          <img src="../img/container/other.gif" alt="" />&#160;(<xsl:value-of select="$locale/text[@id='other']" />)
                        </xsl:when>
                        <xsl:when test="$container = 'not chosen' or $container = 'not_chosen'">
                          <img src="../img/container/not_chosen.gif" alt="" />&#160;(<xsl:value-of select="$locale/text[@id='not_chosen']" />)
                        </xsl:when>
                      </xsl:choose>
                    </xsl:if>
                  </p>
                  <div class="clear"><xsl:text disable-output-escaping="yes"><![CDATA[]]></xsl:text></div>
                </div>
              </div>
            <!--<p>
              <xsl:if test="string(normalize-space(grdspk:cache/grdspk:state))">
                <xsl:value-of select="$locale/text[@id='location']" />&#160;<xsl:value-of select="grdspk:cache/grdspk:state" />, <xsl:value-of select="grdspk:cache/grdspk:country" />
              </xsl:if>
          </p>-->

            <xsl:if test='grdspk:cache/grdspk:attributes/grdspk:attribute'>
                <ul class="cacheAttributes">
                    <xsl:apply-templates select="grdspk:cache/grdspk:attributes/grdspk:attribute" />
                </ul>
            </xsl:if>

            <xsl:if test='$display_note'>
              <div class="cacheNote">
                <p><xsl:value-of select="$locale/text[@id='note']" /></p>
              </div>
            </xsl:if>
            <!-- short_description -->
            <xsl:if test='$display_short_desc and normalize-space(grdspk:cache/grdspk:short_description)'>
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
            </xsl:if>

            <!-- long_description -->
            <xsl:if test='$display_long_desc and normalize-space(grdspk:cache/grdspk:long_description)'>
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
            </xsl:if>

            <!-- Additional Hints -->
            <xsl:if test='$display_hint'>
              <xsl:apply-templates select="grdspk:cache/grdspk:encoded_hints" />
            </xsl:if>

            <!-- Logs -->
            <xsl:if test='$display_logs and grdspk:cache/grdspk:logs/grdspk:log'>
              <div class="cacheLogs">
                <p><xsl:value-of select="$locale/text[@id='logs']" /></p>
                <table>
                  <xsl:apply-templates select="grdspk:cache/grdspk:logs/grdspk:log" />
                </table>
              </div>
            </xsl:if>
            </div>
          </xsl:when>

          <!-- Waypoints -->
          <xsl:otherwise>
              <xsl:if test='$display_waypoints'>
                <!-- variables -->
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

                <p class="waypoint">
                  <xsl:value-of select="gpx:sym"/> (<xsl:value-of select="gpx:name"/>) &#8212; <strong><xsl:value-of select="$lat"/>&#160;<xsl:value-of select="$lon"/></strong>
                  <xsl:if test="gpx:cmt != ''">
                    <br /><xsl:value-of select="gpx:cmt"/>
                  </xsl:if>
                </p>
              </xsl:if>
          </xsl:otherwise>

          </xsl:choose>


          </xsl:for-each>
      </body>
</html>
</xsl:template>
</xsl:stylesheet>
