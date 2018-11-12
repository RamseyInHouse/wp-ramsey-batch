<?php
namespace RamseySolutions\RamseyBatch\Views;

abstract class AdminPage {

	protected $controller;
	protected $slug;
	protected $title;

	abstract public function display();

	public function __construct(object $controller, string $slug, string $title) {
		$this->controller = $controller;
		$this->slug = $slug;
		$this->title = $title;
	}

	protected function open() {
		return '<div id="' . $this->slug . '" class="wrap"><div id="icon-options-general" class="icon32"><br></div><h1 class="wp-heading-inline">' . $this->title . '</h1>';
	}

	protected function close() {
		return '</div>';//.wrap
	}

	public function debug() {
		dump(__CLASS__);
		dump(dirname(__FILE__));
		dump(get_current_screen());
		dump($this->controller);
		dump(RB_PLUGIN_ROOT);
		dump(RB_PLUGIN_URL);
	}

}