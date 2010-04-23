<div id="topbar" class="black">
	<div id="title">Opportunities</div>
	<div id="leftnav">
		<a href="{devblocks_url}{/devblocks_url}iphone/"><img alt="home" src="{devblocks_url}c=resource&p=cerberusweb.iphone&f=Framework/images/home.png{/devblocks_url}" /></a>
		<a href="{devblocks_url}{/devblocks_url}iphone/activity/">Activity</a>
	</div>
</div>

<div id="content">
	<ul class="pageitem">
		{foreach from=$opportunities item=opp}
		<li class="menu"><a href="{devblocks_url}{/devblocks_url}iphone/activity/opportunities/display/{$opp->id}/">
			<span class="name">{$opp->name}</span><span class="arrow"></span></a>
		</li>
		{/foreach}
	</ul>
</div>