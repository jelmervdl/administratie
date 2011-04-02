google.load('jquery', '1.2.6');

add_onload_listener(function(root) {
	google.setOnLoadCallback(function() {		
		$(root).find('span.inline_edit').each(function() {
		
			var mode = 'show';
		
			var element = this;
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
				$(input_element)
					.bind('blur', switch_to_show)
					.bind('keydown', check_key);
			}
		
			var switch_to_show = function() {
				mode = 'show';
			
				if(inner_text.data !== input_element.value) {
					$.post($(element).attr('rel'), {data: input_element.value});
				}
			
				inner_text.data = input_element.value;
				element.replaceChild(inner_text, input_element);
			}
		
			$(element).click(function() {
				if(mode == 'show') {
					switch_to_edit();
				}
			});
		});
	});
});