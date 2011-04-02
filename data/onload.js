(function() {
	var callbacks = [];
	
	window.add_onload_listener = function(callback)
	{
		callbacks.push(callback);
	}
	
	window.notify_onload_listeners = function(root)
	{
		for (var i = 0; i < callbacks.length; ++i)
			callbacks[i].apply(document, [root]);
	}
	
	window.addEventListener('DOMContentLoaded', function() {
		window.notify_onload_listeners(document);
	});
})();