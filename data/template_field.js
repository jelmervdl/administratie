function template_field(selector)
{
	var setValue = function(field, value)
	{
		if (field.nodeName == 'SELECT')
			for (var i = 0; i < field.options.length; ++i)
				if (field.options[i].value == value)
					return field.selectedIndex = i;
		else
			field.value = value;
	};
	
	var option = selector.options[selector.selectedIndex];
	
	if (!option.dataset['prefill'])
		return;

	var data = JSON.parse(option.dataset['prefill']);

	for (var selector in data)
		setValue(document.querySelector(selector), data[selector]);
}

