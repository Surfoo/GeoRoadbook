<h1 id="tocTitle">Table of contents</h1>

<table id="toc">
    <thead>
        <tr>
            <th colspan="2" id="tocHeadName">Name</th>
            <th id="tocHeadFound"><img src="../img/log/icon_smile.png" /></th>
            <th id="tocHeadDnf"><img src="../img/log/icon_sad.png" /></th>
            <th id="tocHeadPage">Page</th>
        </tr>
    </thead>
    {if !empty($toc)}
        <tbody>
        {foreach from=$toc item=cache}
        <tr>
            <td class="tocIcon"><img src="{$cache.icon|escape}" style="width: 16px;height:16px" /></td>
            <td class="tocName">{$cache.title|escape}</td>
            <td class="tocFound"> </td>
            <td class="tocDnf"> </td>
            <td class="tocPage"> </td>
        </tr>
        {/foreach}
        </tbody>
    {/if}
</table>

<p class="pagebreak"><!-- pagebreak --></p>
