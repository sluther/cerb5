<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmFeedItemPopup">
<input type="hidden" name="c" value="feeds">
<input type="hidden" name="a" value="saveFeedItemPopup">
{if !empty($model) && !empty($model->id)}<input type="hidden" name="id" value="{$model->id}">{/if}
<input type="hidden" name="do_delete" value="0">

<fieldset>
	<legend>{'common.properties'|devblocks_translate}</legend>

	<table cellspacing="0" cellpadding="2" border="0" width="98%">
		<tr>
			<td width="1%" valign="top" nowrap="nowrap"><b>{'common.title'|devblocks_translate|capitalize}:</b></td>
			<td width="99%" valign="top">
				{$model->title}
			</td>
		</tr>
		<tr>
			<td width="1%" valign="top" nowrap="nowrap"><b>{'common.url'|devblocks_translate|upper}:</b></td>
			<td width="99%" valign="top">
				<a href="{$model->url}" target="_blank">{$model->url}</a>
			</td>
		</tr>
		<tr>
			<td width="1%" valign="top" nowrap="nowrap"><b>{'common.status'|devblocks_translate|capitalize}:</b></td>
			<td width="99%" valign="top">
				<label><input type="radio" name="is_closed" value="0" {if empty($model) || !$model->is_closed}checked="checked"{/if}> {'status.open'|devblocks_translate|capitalize}</label>
				<label><input type="radio" name="is_closed" value="1" {if !empty($model) && $model->is_closed}checked="checked"{/if}> {'status.closed'|devblocks_translate|capitalize}</label>
			</td>
		</tr>
		<tr>
			<td width="1%" valign="top" nowrap="nowrap"><b>{'common.owners'|devblocks_translate|capitalize}:</b></td>
			<td width="99%" valign="top">
				<button type="button" class="chooser_worker"><span class="cerb-sprite sprite-view"></span></button>
				<ul class="chooser-container bubbles" style="display:block;">
				{if !empty($context_workers)}
					{foreach from=$context_workers item=context_worker}
					<li>{$context_worker->getName()}<input type="hidden" name="worker_id[]" value="{$context_worker->id}"><a href="javascript:;" onclick="$(this).parent().remove();"><span class="ui-icon ui-icon-trash" style="display:inline-block;width:14px;height:14px;"></span></a></li>
					{/foreach}
				{/if}
				</ul>
			</td>
		</tr>
	</table>
</fieldset>

{if !empty($custom_fields)}
<fieldset>
	<legend>{'common.custom_fields'|devblocks_translate}</legend>
	{include file="devblocks:cerberusweb.core::internal/custom_fields/bulk/form.tpl" bulk=false}
</fieldset>
{/if}

{* Comment *}
{if !empty($last_comment)}
	{include file="devblocks:cerberusweb.core::internal/comments/comment.tpl" readonly=true comment=$last_comment}
{/if}

<fieldset>
	<legend>{'common.comment'|devblocks_translate|capitalize}</legend>
	<textarea name="comment" rows="5" cols="45" style="width:98%;"></textarea><br>
	<b>{'common.notify_workers'|devblocks_translate}:</b>
	<button type="button" class="chooser_notify_worker"><span class="cerb-sprite sprite-view"></span></button>
	<ul class="chooser-container bubbles" style="display:block;">
		{if !empty($context_workers)}
			{foreach from=$context_workers item=context_worker}
			{if $context_worker->id != $active_worker->id}
				<li>{$context_worker->getName()}<input type="hidden" name="notify_worker_ids[]" value="{$context_worker->id}"><a href="javascript:;" onclick="$(this).parent().remove();"><span class="ui-icon ui-icon-trash" style="display:inline-block;width:14px;height:14px;"></span></a></li>
			{/if}
			{/foreach}
		{/if}
	</ul>
</fieldset>

<button type="button" onclick="genericAjaxPopupPostCloseReloadView('peek','frmFeedItemPopup','{$view_id}',false,'feeditem_save');"><span class="cerb-sprite sprite-check"></span> {$translate->_('common.save_changes')|capitalize}</button>
{if $model->id && ($active_worker->is_superuser || $active_worker->id == $model->worker_id)}<button type="button" onclick="if(confirm('Permanently delete this feed item?')) { this.form.do_delete.value='1';genericAjaxPopupPostCloseReloadView('peek','frmFeedItemPopup','{$view_id}'); } "><span class="cerb-sprite sprite-forbidden"></span> {$translate->_('common.delete')|capitalize}</button>{/if}

{if !empty($model->id)}
<div style="float:right;">
	<a href="{devblocks_url}c=feeds&i=item&id={$model->id}{/devblocks_url}">view full record</a>
</div>
{/if}
</form>

<script type="text/javascript">
	$popup = genericAjaxPopupFetch('peek');
	$popup.one('popup_open', function(event,ui) {
		$(this).dialog('option','title',"{$translate->_('feeds.item')}");
	});
	$('#frmFeedItemPopup button.chooser_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','worker_id', { autocomplete:true });
	});
	$('#frmFeedItemPopup button.chooser_notify_worker').each(function() {
		ajax.chooser(this,'cerberusweb.contexts.worker','notify_worker_ids', { autocomplete:true });
	});
</script>
