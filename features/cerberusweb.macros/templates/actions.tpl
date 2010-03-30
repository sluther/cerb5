{foreach from=$actions item=action key=action_id}
<tr>
	<td valign="top">
		<input type="checkbox" name="do[]" value="{$action->id}">{$action->name}
	</td>
</tr>
{/foreach}