/* Sheets */
(function() {

	var _duration = 350;
	
	var _open_sheet = null;
	
	function defer(callback)
	{
		setTimeout(callback, 1);
	}
	
	function set_transition(node, transition)
	{
		node.style.transition       = transition;
		node.style.mozTransition    = transition;
		node.style.webkitTransition = transition;
	}
	
	function get_sheet(sheet)
	{
		if (typeof sheet == "string")
			sheet = document.getElementById(sheet);
		else
			while (sheet && !(/(^|\s)sheet($|\s)/.test(sheet.className)))
				sheet = sheet.parentNode;
		
		return sheet;
	}

	window.show_sheet = function(sheet, callback) {
		try {
			var sheet_node = get_sheet(sheet);
			
			if (!sheet_node)
				return false;
			
			if (_open_sheet)
			{
				window.hide_sheet(_open_sheet, function() {
					window.show_sheet(sheet_node, callback);
				});
				return true;
			}
			
			_open_sheet = sheet_node;
			
			sheet_node.style.visibility = 'hidden';
			
			sheet_node.style.display = 'block';
			
			var height = sheet_node.clientHeight;
			
			var frame = 0;
			
			sheet_node.style.top = -height + 'px';
			
			sheet_node.style.visibility = 'visible';
			
			if (sheet_node.hasAttribute('onsheetopen'))
				new Function(sheet_node.getAttribute('onsheetopen')).call(sheet_node);
			
			defer(function() {
				set_transition(sheet_node, 'top ease-out ' + _duration + 'ms');
				sheet_node.style.top = '0px';
				
				if (callback)
					setTimeout(callback, _duration);
			});
			
			return true;
		} catch(e) {
			return false;
		}
	}

	window.hide_sheet = function(sheet, callback) {
		try {
			var sheet_node = get_sheet(sheet);
			
			if (!sheet_node)
				return false;
			
			if (_open_sheet == sheet_node)
				_open_sheet = null;
			
			var height = sheet_node.clientHeight;
			
			var frame = 0;
			
			setTimeout(function() {
				sheet_node.style.display = 'none';
					
				if (callback)
					callback();
				
				if (sheet_node.hasAttribute('onsheetclose'))
					new Function(sheet_node.getAttribute('onsheetclose')).call(sheet_node);
			}, _duration);
			
			set_transition(sheet_node, 'top ease-in ' + _duration + 'ms');
			sheet_node.style.top = -height + 'px';
			
			return true;
		} catch(e) {
			return false;
		}
	}
	
	window.load_sheet = function(url, callback)
	{
		var request = new XMLHttpRequest();
		request.open('GET', url);
		request.setRequestHeader('X-Load-Sheet', 'yes'); // circomvent template
		request.setRequestHeader('X-Origin', document.location.href); // know what the save button does
		request.onreadystatechange = function() {
			if (request.readyState == 4 && request.status == 200)
			{
				var sandbox = document.createElement('div');
				sandbox.innerHTML = request.responseText;
				
				window.notify_onload_listeners(sandbox);
				
				var sheet = sandbox.firstChild;
				sheet.style.display = 'none';
				sheet.setAttribute('onsheetclose', 'window.unload_sheet(this)');
				document.body.appendChild(sheet);
				window.show_sheet(sheet, callback);
			}
		}
		request.send();
	}
	
	window.unload_sheet = function(sheet_id)
	{
		var sheet = get_sheet(sheet_id);
		
		if (sheet)
			sheet.parentNode.removeChild(sheet);
	}
	
	window.addEventListener('keydown', function(e) {
		if (e.keyCode == 27 && _open_sheet)
		{
			window.hide_sheet(_open_sheet);
			e.preventDefault();
		}
	}, false);

})();
/* Placeholder */
(function() {
	
	var Placeholder = function(dom_node) {
		var _dom_node = dom_node;
		
		var _attach_event_listeners = function() {
			_dom_node.addEventListener('focus', _clear, false);
			_dom_node.addEventListener('blur', _fill, false);
			if(_dom_node.form)
				_dom_node.form.addEventListener('submit', _clear, false);
		}
		
		var _clear = function() {
			if(_dom_node.value == _dom_node.getAttribute('data-placeholder')) {
				_dom_node.value = '';
			}
			
			_dom_node.className = _dom_node.className.replace(/\s*placeholder/, '');
		}
		
		var _fill = function() {
			if(_dom_node.value == '') {
				_dom_node.value = _dom_node.getAttribute('data-placeholder');
				_dom_node.className += ' placeholder';
			}
		}
		
		_attach_event_listeners();
		_fill();
	}
	
	add_onload_listener(function(root) {
		var nodes = root.getElementsByTagName('input');
		
		for(var i = 0; i < nodes.length; i++) {
			if(nodes[i].hasAttribute('data-placeholder')) {
				new Placeholder(nodes[i]);
			}
		}
	}, false);
})();

add_onload_listener(function(root) {
	var input_nodes = root.getElementsByTagName('input');
	
	for (var i = 0; i < input_nodes.length; i++) {
		var node = input_nodes[i];
		if (node.type == 'checkbox' && node.parentNode.parentNode.nodeName == 'TR') {
			(function(node) { // functie tegen het memorization probleem
				node.parentNode.parentNode.addEventListener('click', function(e) {
					if (e.srcElement.type != 'checkbox') {
						node.checked = !node.checked;
						e.preventDefault();
					}
				}, false);
			})(node);
		}
	}
	
	var links = root.getElementsByClassName('open-in-sheet');
	for (var i = 0; i < links.length; ++i)
		links[i].addEventListener('click', function(e) {
			window.load_sheet(e.target.href);
			e.preventDefault();
		}, false);
	
}, false);