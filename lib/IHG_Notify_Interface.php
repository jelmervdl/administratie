<?php

interface IHG_Notify_Interface {
	public function notify($event_name, array $context);
}