
var DatePicker = function(dom_node) {

	var date_elements = [
		/* day */	new DatePicker.DateElement([0,  2],  [1, 31], '##'),
		/* month */	new DatePicker.DateElement([3,  5],  [1, 12], '##'),
		/* year */	new DatePicker.DateElement([6,  10], [1970, 2038], '####'),
		/* hour */	new DatePicker.DateElement([11, 13], [0, 23], '##'),
		/* minute */new DatePicker.DateElement([14, 16], [0, 59], '##')
		/* second */ //new DatePicker.DateElement([17, 19], [0, 59], '##')
	]
	
	var _dom_node = dom_node;
	
	var _attach_event_listeners = function() {
		_dom_node.addEventListener('click', _click_callback, false);
		_dom_node.addEventListener('keydown', _key_callback, false);
		_dom_node.addEventListener('DOMMouseScroll', _scroll_callback, false);
	}
	
	var _click_callback = function(e) {
		var date_element = _get_date_element();
		if(date_element !== null) {
			_set_selection_range(date_element);
		}
	}
	
	var _key_callback = function(e) {
		var date_element = _get_date_element();
		
		if(e.keyCode < 37 || e.keyCode > 40 || date_element === null) return;
		
		_sync_date_elements();
		
		if(e.keyCode == 37 && date_element > 0) {
			date_element--;
		}
		else if(e.keyCode == 39 && date_element < date_elements.length - 1) {
			date_element++;
		}
		else if(e.keyCode == 38) {
			date_elements[date_element].increment();
		}
		else if(e.keyCode == 40) {
			date_elements[date_element].decrement();
		}
		
		e.preventDefault();
		
		_set_node_value();
		
		_set_selection_range(date_element);
	}
	
	var _scroll_callback = function(e) {
		var date_element = _get_date_element();
		
		if(!date_element) return;
		
		_sync_date_elements();
		
		var wheelData = (e.detail ? e.detail * -1 : e.wheelDelta / 40) / 2;
		
		if(wheelData > 0) {
			while(wheelData-- > 0) date_elements[date_element].increment();
		}
		else {
			while(wheelData++ < 0) date_elements[date_element].decrement();
		}
		
		e.preventDefault();
		
		_set_node_value();
		
		_set_selection_range(date_element);
	}
	
	var _get_date_element = function() {
		//if(!_dom_node.value.match(/^\d{2}\-\d{2}\-\d{4}\s\d{2}:\d{2}:\d{2}$/)) {
		if(!_dom_node.value.match(/^\d{2}\-\d{2}\-\d{4}\s\d{2}:\d{2}$/)) {
			return null;
		}
		
		for(var i in date_elements) {
			var position_range = date_elements[i].position;
			if(_dom_node.selectionStart >= position_range[0]
				&& _dom_node.selectionEnd <= position_range[1]) return i;
		}
		
		return false;
	}
	
	var _set_node_value = function() {
		_dom_node.value = date_elements[0].print()
			+ '-' + date_elements[1].print()
			+ '-' + date_elements[2].print()
			+ ' ' + date_elements[3].print()
			+ ':' + date_elements[4].print();
			// + ':' + date_elements[5].print();
	}
	
	var _set_selection_range = function(date_element) {
		var position_range = date_elements[date_element].position;
		_dom_node.selectionStart = position_range[0];
		_dom_node.selectionEnd 	 = position_range[1];
	}
	
	var _sync_date_elements = function() {
		for(var i in date_elements) {
			date_elements[i].parseValue(_dom_node.value.substring(
				date_elements[i].position[0],
				date_elements[i].position[1]));
		}
	}
	
	_attach_event_listeners();
}

DatePicker.DateElement = function(position, range, mask) {
	this.position = position;
	this.range = range;
	this.mask = mask;
	this.value = null;
}

DatePicker.DateElement.prototype = {
	parseValue: function(value) {
		this.value = parseInt(value.replace(/^0+(\d+)$/, '$1'));
	},
	
	print: function() {
		value = this.value.toString();
		
		while(value.length < this.mask.length) {
			value = '0' + value;
		}
		
		return value;
	},
	
	increment: function() {
		this.value = this.value + 1 > this.range[1] ? this.range[0] : this.value + 1;
	},
	
	decrement: function() {
		this.value = this.value - 1 < this.range[0] ? this.range[1] : this.value - 1;
	}

}

add_onload_listener(function(root) {
	var input_elements = root.getElementsByTagName('input');
	
	for(var i = 0; i < input_elements.length; i++) {
		if(input_elements[i].className.match(/datepicker/)) {
			new DatePicker(input_elements[i]);
		}
	}
}, true);