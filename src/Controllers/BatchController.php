<?php
namespace RamseySolutions\RamseyBatch\Controllers;

class BatchController {
	
	/**
	 * Registered batch jobs
	 * @var array
	 *      @type string "name"
	 *      @type string "description"
	 *      @type string "lastRunDate"
	 */
	protected $jobs;

    /**
     * Constructor
     */
    public function __construct() {
        $this->jobs = apply_filters(RB_PLUGIN_SLUG . '-jobs', [], $this);
    }

    /**
     * Enqueue JS scripts
     * @return void
     */
    public static function enqueueScripts() {
        wp_register_script(RB_PLUGIN_SLUG, RB_PLUGIN_URL . '/js/src/ramsey-batch.js', ['jquery'], get_plugin_data(RB_PLUGIN_ROOT . '/wp-ramsey-batch.php', false)['Version']);

        $currentScreen = get_current_screen();
        if( $currentScreen->id == 'tools_page_' . RB_PLUGIN_SLUG) {
            wp_enqueue_script(RB_PLUGIN_SLUG);
        }
    }

    public static function register(string $name) {
        if( !class_exists($name) ) return;
        new $name;
    }

    public function getJobs() {
    	return $this->jobs;
    }

}