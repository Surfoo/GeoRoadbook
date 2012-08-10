<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0">

<!-- attributes -->
<xsl:template match="grdspk:cache/grdspk:attributes/grdspk:attribute">
  	<li>
    <xsl:choose>
        <xsl:when test="(. = 'Dogs' or . = 'Dogs allowed')">
            <img src="../img/attributes/dogs-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test="(. = 'Dogs' or . = 'Dogs allowed')">
            <img src="../img/attributes/dogs-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Snowmobiles'">
            <img src="../img/attributes/snowmobiles-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Snowmobiles'">
            <img src="../img/attributes/snowmobiles-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Off-road vehicles'">
            <img src="../img/attributes/jeeps-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Off-road vehicles'">
            <img src="../img/attributes/jeeps-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Quads'">
            <img src="../img/attributes/quads-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Quads'">
            <img src="../img/attributes/quads-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Motorcycles'">
            <img src="../img/attributes/motorcycles-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Motorcycles'">
            <img src="../img/attributes/motorcycles-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Bicycles'">
            <img src="../img/attributes/bicycles-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Bicycles'">
            <img src="../img/attributes/bicycles-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'UV Light Required'">
            <img src="../img/attributes/UV-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Truck Driver/RV'">
            <img src="../img/attributes/rv-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Truck Driver/RV'">
            <img src="../img/attributes/rv-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Flashlight required'">
            <img src="../img/attributes/flashlight-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Flashlight required'">
            <img src="../img/attributes/flashlight-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Boat'">
            <img src="../img/attributes/boat-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Scuba gear'">
            <img src="../img/attributes/scuba-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Campfires'">
            <img src="../img/attributes/campfires-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Campfires'">
            <img src="../img/attributes/campfires-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Climbing gear'">
            <img src="../img/attributes/climbing-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Climbing gear'">
            <img src="../img/attributes/climbing-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Access or parking fee'">
            <img src="../img/attributes/fee-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Horses'">
            <img src="../img/attributes/horses-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Horses'">
            <img src="../img/attributes/horses-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Tree Climbing'">
            <img src="../img/attributes/treeclimbing-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Tree Climbing'">
            <img src="../img/attributes/treeclimbing-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Wireless Beacon'">
            <img src="../img/attributes/wirelessbeacon-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Available during winter'">
            <img src="../img/attributes/winter-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Available during winter'">
            <img src="../img/attributes/winter-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Available at all times'">
            <img src="../img/attributes/available-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Available at all times'">
            <img src="../img/attributes/available-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Recommended at night'">
            <img src="../img/attributes/night-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Recommended at night'">
            <img src="../img/attributes/night-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Snowshoes'">
            <img src="../img/attributes/snowshoes-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Special Tool Required'">
            <img src="../img/attributes/s-tool-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Cross Country Skis'">
            <img src="../img/attributes/skiis-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'May require swimming'">
            <img src="../img/attributes/swimming-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'May require wading'">
            <img src="../img/attributes/wading-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Difficult climbing'">
            <img src="../img/attributes/climbing-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Difficult climbing'">
            <img src="../img/attributes/climbing-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Significant Hike'">
            <img src="../img/attributes/hiking-yes" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Significant Hike'">
            <img src="../img/attributes/hiking-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Scenic view'">
            <img src="../img/attributes/scenic-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Scenic view'">
            <img src="../img/attributes/scenic-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Takes less than an hour'">
            <img src="../img/attributes/onehour-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Takes less than an hour'">
            <img src="../img/attributes/onehour-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Recommended for kids'">
            <img src="../img/attributes/kids-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Recommended for kids'">
            <img src="../img/attributes/kids-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Teamwork Required'">
            <img src="../img/attributes/teamwork-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Teamwork Required'">
            <img src="../img/attributes/teamwork-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Front Yard (Private Residence)'">
            <img src="../img/attributes/frontyard-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Front Yard (Private Residence)'">
            <img src="../img/attributes/frontyard-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Tourist Friendly'">
            <img src="../img/attributes/touristOK-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Tourist Friendly'">
            <img src="../img/attributes/touristOK-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Seasonal Access'">
            <img src="../img/attributes/seasonal-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Seasonal Access'">
            <img src="../img/attributes/seasonal-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Dangerous Animals'">
            <img src="../img/attributes/dangerousanimals-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Poison plants'">
            <img src="../img/attributes/poisonoak-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Poison plants'">
            <img src="../img/attributes/poisonoak-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Long Hike (+10km)'">
            <img src="../img/attributes/hike_long-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Long Hike (+10km)'">
            <img src="../img/attributes/hike_long-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Medium hike (1km-10km)'">
            <img src="../img/attributes/hike_med-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Medium hike (1km-10km)'">
            <img src="../img/attributes/hike_med-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Short hike (less than 1km)'">
            <img src="../img/attributes/hike_short-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Short hike (less than 1km)'">
            <img src="../img/attributes/hike_short-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Abandoned Structure'">
            <img src="../img/attributes/AbandonedBuilding-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Abandoned Structure'">
            <img src="../img/attributes/AbandonedBuilding-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Park and Grab'">
            <img src="../img/attributes/parkngrab-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Park and Grab'">
            <img src="../img/attributes/parkngrab-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Night Cache'">
            <img src="../img/attributes/nightcache-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Night Cache'">
            <img src="../img/attributes/nightcache-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Field Puzzle'">
            <img src="../img/attributes/field_puzzle-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Field Puzzle'">
            <img src="../img/attributes/field_puzzle-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Watch for livestock'">
            <img src="../img/attributes/cow-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Stealth required'">
            <img src="../img/attributes/stealth-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Stealth required'">
            <img src="../img/attributes/stealth-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Camping available'">
            <img src="../img/attributes/camping-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Camping available'">
            <img src="../img/attributes/camping-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Picnic tables nearby'">
            <img src="../img/attributes/picnic-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Picnic tables nearby'">
            <img src="../img/attributes/picnic-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Telephone nearby'">
            <img src="../img/attributes/phone-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Telephone nearby'">
            <img src="../img/attributes/phone-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Public restrooms nearby'">
            <img src="../img/attributes/restrooms-yes" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Public restrooms nearby'">
            <img src="../img/attributes/restrooms-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Drinking water nearby'">
            <img src="../img/attributes/water-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Drinking water nearby'">
            <img src="../img/attributes/water-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Public transportation'">
            <img src="../img/attributes/public-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Parking available'">
            <img src="../img/attributes/parking-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Parking available'">
            <img src="../img/attributes/parking-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Wheelchair accessible'">
            <img src="../img/attributes/wheelchair-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Wheelchair accessible'">
            <img src="../img/attributes/wheelchair-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Dangerous area'">
            <img src="../img/attributes/danger-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Hunting'">
            <img src="../img/attributes/hunting-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Cliff / falling rocks'">
            <img src="../img/attributes/cliff-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Abandoned mines'">
            <img src="../img/attributes/mine-yes" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Ticks'">
            <img src="../img/attributes/ticks-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Stroller accessible'">
            <img src="../img/attributes/stroller-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Stroller accessible'">
            <img src="../img/attributes/stroller-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Thorns'">
            <img src="../img/attributes/thorn-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Food Nearby'">
            <img src="../img/attributes/food-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Food Nearby'">
            <img src="../img/attributes/food-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Fuel Nearby'">
            <img src="../img/attributes/fuel-yes.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Fuel Nearby'">
            <img src="../img/attributes/fuel-no.gif" alt="" />
        </xsl:when>
        <xsl:when test=". = 'Needs maintenance'">
            <img src="../img/attributes/firstaid-yes.gif" alt="" />
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="." />  
        </xsl:otherwise>
    </xsl:choose> 
  	</li>
</xsl:template>

</xsl:stylesheet>