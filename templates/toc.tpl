<h1 id="tocTitle">{{ i18n.title }}</h1>

<table id="toc">
    <thead>
        <tr>
            <th id="tocHeadNumber">#</th>
            <th colspan="2" id="tocHeadName">{{ i18n.name }}</th>
            <th id="tocHeadFound"><img src="../img/log/icon_smile.png" alt="" /></th>
            <th id="tocHeadDnf"><img src="../img/log/icon_sad.png" alt="" /></th>
            <th id="tocHeadPage">{{ i18n.page }}</th>
        </tr>
    </thead>
    {% if content is not empty %}
        <tbody>
        {% for cache in content %}
        <tr>
            <td class="tocNumber">{{ loop.index }}</td>
            <td class="tocIcon"><img src="{{ cache.icon|e }}" style="width: 16px;height:16px" /></td>
            <td class="tocName">{{ cache.title|e }}</td>
            <td class="tocFound"> </td>
            <td class="tocDnf"> </td>
            <td class="tocPage"> </td>
        </tr>
        {% endfor %}
        </tbody>
    {% endif %}
</table>

<p class="pagebreak"><!-- pagebreak --></p>
