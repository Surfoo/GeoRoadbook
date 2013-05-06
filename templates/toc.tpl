<h1 id="tocTitle">{$i18n.title}</h1>

<table id="toc">
    <thead>
        <tr>
            <th colspan="2" id="tocHeadName">{$i18n.name}</th>
            <th id="tocHeadFound"><img src="../img/log/icon_smile.png" /></th>
            <th id="tocHeadDnf"><img src="../img/log/icon_sad.png" /></th>
            <th id="tocHeadPage">{$i18n.page}</th>
        </tr>
    </thead>
    {if !empty($content)}
        <tbody>
        {foreach from=$content item=cache}
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
