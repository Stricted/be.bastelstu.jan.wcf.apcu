<dl>
	<dt>{lang}wcf.acp.option.cache_source_type{/lang}</dt>
	<dd>
		{assign var='__source' value='\\'|explode:$server[cache]}
		{lang}wcf.acp.cache.source.type.{$__source|array_pop}{/lang}
		<small>{$server[cache]}</small>
	</dd>
</dl>