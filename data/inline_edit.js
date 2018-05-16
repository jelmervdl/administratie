add_onload_listener(function(root) {
	document.querySelectorAll('span.inline_edit').forEach(function(element) {
		var mode = 'show';
	
		var inner_text = element.firstChild;
		var input_element = document.createElement('input');
		input_element.type = 'text';
	
		var check_key = function(e) {
			switch(e.keyCode) {
				case 27: // escape
					input_element.value = inner_text.data;
				case 13:
					switch_to_show();
					return false;
			}
		}
	
		var switch_to_edit = function() {
			mode = 'edit';
			input_element.value = inner_text.data;
			element.replaceChild(input_element, inner_text);
			input_element.focus();
			input_element
				.addEventListener('blur', switch_to_show)
				.addEventListener('keydown', check_key);
		}
	
		var switch_to_show = function() {
			mode = 'show';
		
			if(inner_text.data !== input_element.value) {
				var data = new FormData();
				data.append('data', input_element.value);
				fetch(new Request(element.getAttribute('rel'), {method: 'POST', body: data}));
			}
		
			inner_text.data = input_element.value;
			element.replaceChild(inner_text, input_element);
		}
	
		element.addEventListener('click', function() {
			if(mode == 'show') {
				switch_to_edit();
			}
		});
	});
});