UPDATE wcf1_option SET 
	selectOptions = 'disk:wcf.acp.option.cache_source_type.disk
memcached:wcf.acp.option.cache_source_type.memcached
apc:wcf.acp.option.cache_source_type.apc
apcu:wcf.acp.option.cache_source_type.apcu
no:wcf.acp.option.cache_source_type.no',
	enableOptions = 'disk:!cache_source_memcached_host
memcached:cache_source_memcached_host
apc:!cache_source_memcached_host
apcu:!cache_source_memcached_host
no:!cache_source_memcached_host' 
	WHERE optionName = 'cache_source_type';