{extends file="templates/base.tpl"}
{block name=title}Scene #{$scene.id}: {$scene.title}{/block}

{block name=contents}
<table id="posetable" class="table-scenesys">
	<thead>
	<tr>
	    <th class="cell-channelname">Type</th>
		<th class="cell-owner">Owner</th>
		<th class="cell-pose">Text</th>
	</tr>
	</thead>
	<tbody>
	{foreach $poses as $pose}
	<tr class='pose'>
	    <td class="cell-channelname">{$pose.channel_category}</th>
	    <td class="cell-owner">
            {if $pose.has_owner}
                <a href="owner.php?id={$pose.owner_id}">{$pose.display_name}</a>
            {else}
                System
            {/if}
	    </td>
		<td class="cell-pose">{$pose.text}
	</tr>
	{/foreach}
	</table>
{/block}