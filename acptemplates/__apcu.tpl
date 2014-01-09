{if $cacheData.apcusize|isset}<dl>
	<dt>{lang}wcf.acp.cache.data.apcusize{/lang}</dt>
	<dd>{@$cacheData.apcusize|filesize}</dd>
</dl>{/if}
{if $cacheData.apcufiles|isset}<dl>
	<dt>{lang}wcf.acp.cache.data.apcufiles{/lang}</dt>
	<dd>{#$cacheData.apcufiles}</dd>
</dl>{/if}
{if $cacheData.apcuhits|isset}<dl>
	<dt>{lang}wcf.acp.cache.data.apcuhits{/lang}</dt>
	<dd>{#$cacheData.apcuhits}</dd>
</dl>{/if}