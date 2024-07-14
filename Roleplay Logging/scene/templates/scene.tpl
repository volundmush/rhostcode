{extends file="templates/base.tpl"}
{block name=title}Scene #{$scene.id}: {$scene.title}{/block}

{block name=contents}
<table id="posetable" class="table-scenesys">
	<thead>
	<tr>
		<th class="cell-owner">Owner</th>
		<th class="cell-pose">Pose</th>
	</tr>
	</thead>
	<tbody>
	{foreach $poses as $pose}
	<tr class='pose'>
		<td class="cell-owner"><a href="owner.php?id={$pose.owner}">{$pose.owner_name}</a></td>
		<td class="cell-pose">{$pose.text}</td>
	</tr>
	{/foreach}
	</table>
{/block}