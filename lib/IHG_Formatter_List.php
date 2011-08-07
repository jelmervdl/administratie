<?php

class IHG_Formatter_List
{
	private $lines;
	
	private $i;
	
	public $text_formatter;
	
	protected $line;
	
	public function __construct($text_formatter = null)
	{
		if ($text_formatter && !is_callable($text_formatter))
			throw new InvalidArgumentException('Text formatter is not callable');
		
		$this->text_formatter = $text_formatter ?: array($this, 'default_text_formatter');
	}
	
	public function __invoke($sanitzed)
	{
		$this->reset($this->split_lines($sanitzed));
	
		$n = 0;
	
		do
		{
			$this->next();
			
			if ($this->is_list_item())
			{
				if (!$this->list_is_open)
					$this->open_list();
			
				$this->line = $this->format_list_item($this->line);
			}
			else
			{
				if ($this->list_is_open)
					$this->close_list();
				
				if (!is_null($this->line))
					$this->line = $this->format_text($this->line, false);
			}
		}
		while(!$this->eof() && $n++ < 10);
		
		return implode($this->lines);
	}
	
	protected function reset($lines)
	{
		$this->lines = $lines;
		
		$this->i = -1;
		
		$this->list_is_open = false;
	}
	
	protected function seek($i)
	{
		$this->i = $i - 1;
		$this->next();
	}
	
	protected function next()
	{
		$this->line =& $this->lines[++$this->i];
	}
	
	protected function eof()
	{
		return is_null($this->line);
	}
	
	protected function insert($line)
	{
		array_splice($this->lines, $this->i, 0, $line);
		$this->next();
	}
	
	protected function remove()
	{
		array_splice($this->lines, $this->i, 1);
		$this->seek($this->i - 1);
	}
	
	protected function split_lines($text)
	{
		$lines_and_delimiters = preg_split("/(\n)/", $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		
		$lines = array();
		
		for ($i = 0, $n = count($lines_and_delimiters); $i < $n; $i += 2)
			$lines[] = $lines_and_delimiters[$i] . ($i + 1 < $n ? $lines_and_delimiters[$i + 1] : "");
		
		return $lines;
	}
	
	protected function is_list_item()
	{
		return substr(ltrim($this->line), 0, 1) == '-';
	}
	
	protected function is_empty_line()
	{
		return !is_null($this->line) && trim($this->line) == '';
	}
	
	protected function format_list_item($item)
	{
		return sprintf("\t<li>%s</li>\n", $this->format_text(ltrim($item, '- '), true));
	}
	
	protected function format_text($text, $is_inline)
	{
		return call_user_func($this->text_formatter, $is_inline ? trim($text) : $text);
	}
	
	protected function open_list()
	{
		$this->insert("<ul>\n");
		$this->list_is_open = true;
	}
	
	protected function close_list()
	{
		$this->insert("</ul>\n");
		$this->list_is_open = false;
	}
	
	private function default_text_formatter($input)
	{
		return $input;
	}
}
