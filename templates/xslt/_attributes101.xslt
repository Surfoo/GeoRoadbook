<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:gpx="http://www.topografix.com/GPX/1/0"
    xmlns:grdspk="http://www.groundspeak.com/cache/1/0/1">

<!-- attributes -->
<xsl:template match="grdspk:cache/grdspk:attributes/grdspk:attribute">
    <xsl:choose>
        <xsl:when test="(. = 'Dogs' or . = 'Dogs allowed') and @inc = '1'">
            <li><img src="../img/attributes/dogs-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test="(. = 'Dogs' or . = 'Dogs allowed') and @inc = '0'">
            <li><img src="../img/attributes/dogs-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Snowmobiles' and @inc = '1'">
            <li><img src="../img/attributes/snowmobiles-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Snowmobiles' and @inc = '0'">
            <li><img src="../img/attributes/snowmobiles-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Off-road vehicles' and @inc = '1'">
            <li><img src="../img/attributes/jeeps-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Off-road vehicles' and @inc = '0'">
            <li><img src="../img/attributes/jeeps-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Quads' and @inc = '1'">
            <li><img src="../img/attributes/quads-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Quads' and @inc = '0'">
            <li><img src="../img/attributes/quads-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Motorcycles' and @inc = '1'">
            <li><img src="../img/attributes/motorcycles-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Motorcycles' and @inc = '0'">
            <li><img src="../img/attributes/motorcycles-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Bicycles' and @inc = '1'">
            <li><img src="../img/attributes/bicycles-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Bicycles' and @inc = '0'">
            <li><img src="../img/attributes/bicycles-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'UV Light Required' and @inc = '1'">
            <li><img src="../img/attributes/UV-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Truck Driver/RV' and @inc = '1'">
            <li><img src="../img/attributes/rv-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Truck Driver/RV' and @inc = '0'">
            <li><img src="../img/attributes/rv-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Flashlight required' and @inc = '1'">
            <li><img src="../img/attributes/flashlight-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Flashlight required' and @inc = '0'">
            <li><img src="../img/attributes/flashlight-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Boat' and @inc = '1'">
            <li><img src="../img/attributes/boat-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Scuba gear' and @inc = '1'">
            <li><img src="../img/attributes/scuba-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Campfires' and @inc = '1'">
            <li><img src="../img/attributes/campfires-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Campfires' and @inc = '0'">
            <li><img src="../img/attributes/campfires-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Climbing gear' and @inc = '1'">
            <li><img src="../img/attributes/climbing-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Climbing gear' and @inc = '0'">
            <li><img src="../img/attributes/climbing-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Access or parking fee' and @inc = '1'">
            <li><img src="../img/attributes/fee-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Horses' and @inc = '1'">
            <li><img src="../img/attributes/horses-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Horses' and @inc = '0'">
            <li><img src="../img/attributes/horses-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Tree Climbing' and @inc = '1'">
            <li><img src="../img/attributes/treeclimbing-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Tree Climbing' and @inc = '0'">
            <li><img src="../img/attributes/treeclimbing-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Wireless Beacon' and @inc = '1'">
            <li><img src="../img/attributes/wirelessbeacon-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Available during winter' and @inc = '1'">
            <li><img src="../img/attributes/winter-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Available during winter' and @inc = '0'">
            <li><img src="../img/attributes/winter-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Available at all times' and @inc = '1'">
            <li><img src="../img/attributes/available-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Available at all times' and @inc = '0'">
            <li><img src="../img/attributes/available-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Recommended at night' and @inc = '1'">
            <li><img src="../img/attributes/night-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Recommended at night'">
            <li><img src="../img/attributes/night-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Snowshoes' and @inc = '1'">
            <li><img src="../img/attributes/snowshoes-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Special Tool Required' and @inc = '1'">
            <li><img src="../img/attributes/s-tool-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Cross Country Skis' and @inc = '1'">
            <li><img src="../img/attributes/skiis-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'May require swimming' and @inc = '1'">
            <li><img src="../img/attributes/swimming-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'May require wading' and @inc = '1'">
            <li><img src="../img/attributes/wading-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Difficult climbing' and @inc = '1'">
            <li><img src="../img/attributes/climbing-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Difficult climbing' and @inc = '0'">
            <li><img src="../img/attributes/climbing-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Significant Hike'">
            <li><img src="../img/attributes/hiking-yes" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Significant Hike' and @inc = '0'">
            <li><img src="../img/attributes/hiking-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Scenic view' and @inc = '1'">
            <li><img src="../img/attributes/scenic-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Scenic view' and @inc = '0'">
            <li><img src="../img/attributes/scenic-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Takes less than an hour' and @inc = '1'">
            <li><img src="../img/attributes/onehour-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Takes less than an hour' and @inc = '0'">
            <li><img src="../img/attributes/onehour-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Recommended for kids' and @inc = '1'">
            <li><img src="../img/attributes/kids-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Recommended for kids' and @inc = '0'">
            <li><img src="../img/attributes/kids-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Teamwork Required' and @inc = '1'">
            <li><img src="../img/attributes/teamwork-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Teamwork Required' and @inc = '0'">
            <li><img src="../img/attributes/teamwork-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Front Yard (Private Residence)' and @inc = '1'">
            <li><img src="../img/attributes/frontyard-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Front Yard (Private Residence)' and @inc = '0'">
            <li><img src="../img/attributes/frontyard-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Tourist Friendly' and @inc = '1'">
            <li><img src="../img/attributes/touristOK-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Tourist Friendly' and @inc = '0'">
            <li><img src="../img/attributes/touristOK-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Seasonal Access' and @inc = '1'">
            <li><img src="../img/attributes/seasonal-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Seasonal Access' and @inc = '0'">
            <li><img src="../img/attributes/seasonal-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Dangerous Animals' and @inc = '1'">
            <li><img src="../img/attributes/dangerousanimals-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Poison plants' and @inc = '1'">
            <li><img src="../img/attributes/poisonoak-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Poison plants' and @inc = '0'">
            <li><img src="../img/attributes/poisonoak-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Long Hike (+10km)' and @inc = '1'">
            <li><img src="../img/attributes/hike_long-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Long Hike (+10km)' and @inc = '0'">
            <li><img src="../img/attributes/hike_long-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Medium hike (1km-10km)' and @inc = '1'">
            <li><img src="../img/attributes/hike_med-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Medium hike (1km-10km)' and @inc = '0'">
            <li><img src="../img/attributes/hike_med-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Short hike (less than 1km)' and @inc = '1'">
            <li><img src="../img/attributes/hike_short-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Short hike (less than 1km)' and @inc = '0'">
            <li><img src="../img/attributes/hike_short-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Abandoned Structure' and @inc = '1'">
            <li><img src="../img/attributes/AbandonedBuilding-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Abandoned Structure' and @inc = '0'">
            <li><img src="../img/attributes/AbandonedBuilding-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Park and Grab' and @inc = '1'">
            <li><img src="../img/attributes/parkngrab-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Park and Grab' and @inc = '0'">
            <li><img src="../img/attributes/parkngrab-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Night Cache' and @inc = '1'">
            <li><img src="../img/attributes/nightcache-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Night Cache' and @inc = '0'">
            <li><img src="../img/attributes/nightcache-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Field Puzzle' and @inc = '1'">
            <li><img src="../img/attributes/field_puzzle-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Field Puzzle' and @inc = '0'">
            <li><img src="../img/attributes/field_puzzle-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Watch for livestock' and @inc = '1'">
            <li><img src="../img/attributes/cow-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Stealth required' and @inc = '1'">
            <li><img src="../img/attributes/stealth-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Stealth required' and @inc = '0'">
            <li><img src="../img/attributes/stealth-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Camping available' and @inc = '1'">
            <li><img src="../img/attributes/camping-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Camping available' and @inc = '0'">
            <li><img src="../img/attributes/camping-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Picnic tables nearby'">
            <li><img src="../img/attributes/picnic-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Picnic tables nearby' and @inc = '0'">
            <li><img src="../img/attributes/picnic-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Telephone nearby' and @inc = '1'">
            <li><img src="../img/attributes/phone-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Telephone nearby' and @inc = '0'">
            <li><img src="../img/attributes/phone-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Public restrooms nearby' and @inc = '1'">
            <li><img src="../img/attributes/restrooms-yes" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Public restrooms nearby' and @inc = '0'">
            <li><img src="../img/attributes/restrooms-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Drinking water nearby' and @inc = '1'">
            <li><img src="../img/attributes/water-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Drinking water nearby' and @inc = '0'">
            <li><img src="../img/attributes/water-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Public transportation' and @inc = '1'">
            <li><img src="../img/attributes/public-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Parking available' and @inc = '1'">
            <li><img src="../img/attributes/parking-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Parking available' and @inc = '0'">
            <li><img src="../img/attributes/parking-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Wheelchair accessible' and @inc = '1'">
            <li><img src="../img/attributes/wheelchair-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Wheelchair accessible' and @inc = '0'">
            <li><img src="../img/attributes/wheelchair-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Dangerous area' and @inc = '1'">
            <li><img src="../img/attributes/danger-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Hunting' and @inc = '1'">
            <li><img src="../img/attributes/hunting-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Cliff / falling rocks' and @inc = '1'">
            <li><img src="../img/attributes/cliff-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Abandoned mines' and @inc = '1'">
            <li><img src="../img/attributes/mine-yes" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Ticks' and @inc = '1'">
            <li><img src="../img/attributes/ticks-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Stroller accessible' and @inc = '1'">
            <li><img src="../img/attributes/stroller-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Stroller accessible' and @inc = '0'">
            <li><img src="../img/attributes/stroller-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Thorns' and @inc = '1'">
            <li><img src="../img/attributes/thorn-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Food Nearby'">
            <li><img src="../img/attributes/food-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Food Nearby' and @inc = '0'">
            <li><img src="../img/attributes/food-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Fuel Nearby' and @inc = '1'">
            <li><img src="../img/attributes/fuel-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Fuel Nearby' and @inc = '0'">
            <li><img src="../img/attributes/fuel-no.gif" alt="" /></li>
        </xsl:when>
        <xsl:when test=". = 'Needs maintenance' and @inc = '1'">
            <li><img src="../img/attributes/firstaid-yes.gif" alt="" /></li>
        </xsl:when>
        <xsl:otherwise>
            <xsl:value-of select="." />
        </xsl:otherwise>
    </xsl:choose>
</xsl:template>

</xsl:stylesheet>