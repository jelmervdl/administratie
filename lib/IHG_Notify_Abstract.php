<?php

class IHG_Notify_Abstract implements IHG_Notify_Interface {
	
	private $observers = array();
	
	protected function observe($event_name, $callback) {
		$this->observers[$event_name] = $callback;
	}
	
	protected function stopObserving($event_name) {
		unset($this->observers[$event_name]);
	}
	
	public function notify($event_name, array $context) {
		if(!array_key_exists($event_name, $this->observers)) {
			return;
		}
		
		call_user_func_array($this->observers[$event_name], $context);
	}
}
