<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">

<!-- attributes -->
<xsl:template match="grdspk:cache/grdspk:attributes/grdspk:attribute">
    <xsl:choose>
        <xsl:when test="@id = '1' and @inc = '1'">
            <li><img src="../img/attributes/dogs-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '1' and @inc = '0'">
            <li><img src="../img/attributes/dogs-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '2' and @inc = '1'">
            <li><img src="../img/attributes/fee-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '3' and @inc = '1'">
            <li><img src="../img/attributes/climbing-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '3' and @inc = '0'">
            <li><img src="../img/attributes/climbing-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '4' and @inc = '1'">
            <li><img src="../img/attributes/boat-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '5' and @inc = '1'">
            <li><img src="../img/attributes/scuba-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '6' and @inc = '1'">
            <li><img src="../img/attributes/kids-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '6' and @inc = '0'">
            <li><img src="../img/attributes/kids-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '7' and @inc = '1'">
            <li><img src="../img/attributes/onehour-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '7' and @inc = '0'">
            <li><img src="../img/attributes/onehour-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '8' and @inc = '1'">
            <li><img src="../img/attributes/scenic-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '8' and @inc = '0'">
            <li><img src="../img/attributes/scenic-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '9' and @inc = '1'">
            <li><img src="../img/attributes/hiking-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '9' and @inc = '0'">
            <li><img src="../img/attributes/hiking-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '10' and @inc = '1'">
            <li><img src="../img/attributes/climbing-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '10' and @inc = '0'">
            <li><img src="../img/attributes/climbing-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '11' and @inc = '1'">
            <li><img src="../img/attributes/wading-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '12' and @inc = '1'">
            <li><img src="../img/attributes/swimming-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '13' and @inc = '1'">
            <li><img src="../img/attributes/available-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '13' and @inc = '0'">
            <li><img src="../img/attributes/available-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '14' and @inc = '1'">
            <li><img src="../img/attributes/night-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '14' and @inc = '0'">
            <li><img src="../img/attributes/night-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '15' and @inc = '1'">
            <li><img src="../img/attributes/winter-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '15' and @inc = '0'">
            <li><img src="../img/attributes/winter-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '17' and @inc = '1'">
            <li><img src="../img/attributes/poisonoak-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '17' and @inc = '0'">
            <li><img src="../img/attributes/poisonoak-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '18' and @inc = '1'">
            <li><img src="../img/attributes/dangerousanimals-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '19' and @inc = '1'">
            <li><img src="../img/attributes/ticks-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '20' and @inc = '1'">
            <li><img src="../img/attributes/mine-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '21' and @inc = '1'">
            <li><img src="../img/attributes/cliff-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '22' and @inc = '1'">
            <li><img src="../img/attributes/hunting-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '23' and @inc = '1'">
            <li><img src="../img/attributes/danger-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '24' and @inc = '1'">
            <li><img src="../img/attributes/wheelchair-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '24' and @inc = '0'">
            <li><img src="../img/attributes/wheelchair-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '25' and @inc = '1'">
            <li><img src="../img/attributes/parking-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '25' and @inc = '0'">
            <li><img src="../img/attributes/parking-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '26' and @inc = '1'">
            <li><img src="../img/attributes/public-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '27' and @inc = '1'">
            <li><img src="../img/attributes/water-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '27' and @inc = '0'">
            <li><img src="../img/attributes/water-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '28' and @inc = '1'">
            <li><img src="../img/attributes/restrooms-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '28' and @inc = '0'">
            <li><img src="../img/attributes/restrooms-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '29' and @inc = '1'">
            <li><img src="../img/attributes/phone-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '29' and @inc = '0'">
            <li><img src="../img/attributes/phone-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '30' and @inc = '1'">
            <li><img src="../img/attributes/picnic-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '30' and @inc = '0'">
            <li><img src="../img/attributes/picnic-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '31' and @inc = '1'">
            <li><img src="../img/attributes/camping-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '31' and @inc = '0'">
            <li><img src="../img/attributes/camping-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '32' and @inc = '1'">
            <li><img src="../img/attributes/bicycles-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '32' and @inc = '0'">
            <li><img src="../img/attributes/bicycles-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '33' and @inc = '1'">
            <li><img src="../img/attributes/motorcycles-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '33' and @inc = '0'">
            <li><img src="../img/attributes/motorcycles-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '34' and @inc = '1'">
            <li><img src="../img/attributes/quads-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '34' and @inc = '0'">
            <li><img src="../img/attributes/quads-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '35' and @inc = '1'">
            <li><img src="../img/attributes/jeeps-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '35' and @inc = '0'">
            <li><img src="../img/attributes/jeeps-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '36' and @inc = '1'">
            <li><img src="../img/attributes/snowmobiles-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '36' and @inc = '0'">
            <li><img src="../img/attributes/snowmobiles-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '37' and @inc = '1'">
            <li><img src="../img/attributes/horses-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '37' and @inc = '0'">
            <li><img src="../img/attributes/horses-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '38' and @inc = '1'">
            <li><img src="../img/attributes/campfires-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '38' and @inc = '0'">
            <li><img src="../img/attributes/campfires-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '39' and @inc = '1'">
            <li><img src="../img/attributes/thorn-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '40' and @inc = '1'">
            <li><img src="../img/attributes/stealth-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '40' and @inc = '0'">
            <li><img src="../img/attributes/stealth-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '41' and @inc = '1'">
            <li><img src="../img/attributes/stroller-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '41' and @inc = '0'">
            <li><img src="../img/attributes/stroller-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '42' and @inc = '1'">
            <li><img src="../img/attributes/firstaid-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '43' and @inc = '1'">
            <li><img src="../img/attributes/cow-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '44' and @inc = '1'">
            <li><img src="../img/attributes/flashlight-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '44' and @inc = '0'">
            <li><img src="../img/attributes/flashlight-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '45' and @inc = '1'">
            <li><img src="../img/attributes/landf-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '45' and @inc = '0'">
            <li><img src="../img/attributes/landf-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '46' and @inc = '1'">
            <li><img src="../img/attributes/rv-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '46' and @inc = '0'">
            <li><img src="../img/attributes/rv-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '47' and @inc = '1'">
            <li><img src="../img/attributes/field_puzzle-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '47' and @inc = '0'">
            <li><img src="../img/attributes/field_puzzle-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '48' and @inc = '1'">
            <li><img src="../img/attributes/UV-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '49' and @inc = '1'">
            <li><img src="../img/attributes/snowshoes-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '50' and @inc = '1'">
            <li><img src="../img/attributes/skiis-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '51' and @inc = '1'">
            <li><img src="../img/attributes/s-tool-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '52' and @inc = '1'">
            <li><img src="../img/attributes/nightcache-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '52' and @inc = '0'">
            <li><img src="../img/attributes/nightcache-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '53' and @inc = '1'">
            <li><img src="../img/attributes/parkngrab-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '53' and @inc = '0'">
            <li><img src="../img/attributes/parkngrab-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '54' and @inc = '1'">
            <li><img src="../img/attributes/AbandonedBuilding-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '54' and @inc = '0'">
            <li><img src="../img/attributes/AbandonedBuilding-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '55' and @inc = '1'">
            <li><img src="../img/attributes/hike_short-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '55' and @inc = '0'">
            <li><img src="../img/attributes/hike_short-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '56' and @inc = '1'">
            <li><img src="../img/attributes/hike_med-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '56' and @inc = '0'">
            <li><img src="../img/attributes/hike_med-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '57' and @inc = '1'">
            <li><img src="../img/attributes/hike_long-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '57' and @inc = '0'">
            <li><img src="../img/attributes/hike_long-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '58' and @inc = '1'">
            <li><img src="../img/attributes/fuel-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '58' and @inc = '0'">
            <li><img src="../img/attributes/fuel-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '59' and @inc = '1'">
            <li><img src="../img/attributes/food-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '59' and @inc = '0'">
            <li><img src="../img/attributes/food-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '60' and @inc = '1'">
            <li><img src="../img/attributes/wirelessbeacon-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '61' and @inc = '1'">
            <li><img src="../img/attributes/partnership-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '61' and @inc = '0'">
            <li><img src="../img/attributes/partnership-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '62' and @inc = '1'">
            <li><img src="../img/attributes/seasonal-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '62' and @inc = '0'">
            <li><img src="../img/attributes/seasonal-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '63' and @inc = '1'">
            <li><img src="../img/attributes/touristOK-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '63' and @inc = '0'">
            <li><img src="../img/attributes/touristOK-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '64' and @inc = '1'">
            <li><img src="../img/attributes/treeclimbing-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '64' and @inc = '0'">
            <li><img src="../img/attributes/treeclimbing-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '65' and @inc = '1'">
            <li><img src="../img/attributes/frontyard-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '65' and @inc = '0'">
            <li><img src="../img/attributes/frontyard-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '66' and @inc = '1'">
            <li><img src="../img/attributes/teamwork-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="@id = '66' and @inc = '0'">
            <li><img src="../img/attributes/teamwork-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="." />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>
