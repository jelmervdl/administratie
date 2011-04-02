/* Resizable views */
window.DragResize = function(target_node, dom_node) {
	var _target_node = target_node;
	var _dom_node = dom_node;

	var _attach_event_listeners = function() {
		_dom_node.addEventListener('mousedown', _pick_up, false);
	}

	var _pick_up = function(e) {

		window.addEventListener('mousemove', _move, false);
		window.addEventListener('mouseup', _release, false);

		_dom_node.className += ' active';
		
		e.preventDefault();
	}

	var _release = function(e) {
		
		window.removeEventListener('mousemove', _move, false);
		window.removeEventListener('mouseup', _release, false);

		_dom_node.className = _dom_node.className.replace(/\s*active/, '');
	}

	var _move = function(e) {
		_target_node.style.height = Math.max(e.clientY - _target_node.offsetTop - _dom_node.clientHeight / 2, 0) + 'px';
	}
	
	_attach_event_listeners();
}

add_onload_listener(function(root) {
	var nodes = root.getElementsByTagName('*');
	
	var pattern = /(^|\s)drag-resize-handle($|\s)/;
	
	for(var i = 0; i < nodes.length; i++) {
		if(nodes[i].className.match(pattern)) {
			var target_node_id = nodes[i].getAttribute('data-target-node');
			new DragResize(root.getElementById(target_node_id), nodes[i]);
		}
	}
}, false);