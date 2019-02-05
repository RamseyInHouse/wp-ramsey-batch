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

    public static function runJob() {
        if( !wp_doing_ajax() || $_REQUEST['action'] != RB_PLUGIN_SLUG ) return;

        $currentBatchName = stripslashes($_REQUEST['batchName']);
        $batchObj = $GLOBALS[RB_PLUGIN_SLUG][$currentBatchName];

        if( !$batchObj ) {
            wp_send_json_error(['reason' => "Attempted to run job but global object not found - {$currentBatchName}."]);
        }

        $batchObj->run();
    }

    public static function runJobItem() {
        if( !wp_doing_ajax() || $_REQUEST['action'] != 'ramsey-batch-item' ) return;

        $currentBatchName = stripslashes($_REQUEST['batchName']);
        $batchObj = $GLOBALS[RB_PLUGIN_SLUG][$currentBatchName];
        if( !$batchObj ) {
            wp_send_json_error(['reason' => "Attempted to run job item but global object not found - {$currentBatchName}."]);
        }

        $batchObj->runItem();
    }

    /**
     * Enqueue JS scripts
     * @return void
     */
    public static function enqueueScripts() {
        wp_register_script(RB_PLUGIN_SLUG, RB_PLUGIN_URL . '/js/dist/ramsey-batch.min.js', ['jquery'], get_plugin_data(RB_PLUGIN_ROOT . '/wp-ramsey-batch.php', false)['Version']);

        $currentScreen = get_current_screen();
        if( $currentScreen->id == 'tools_page_' . RB_PLUGIN_SLUG) {
            wp_enqueue_script(RB_PLUGIN_SLUG);
        }
    }

    public static function register(string $name) {
        if( !class_exists($name) ) return;

        $obj = new $name;
        add_filter('ramsey-batch-jobs', [$obj, 'registerBatchJob']);

        $GLOBALS[RB_PLUGIN_SLUG][$name] = $obj;
    }

    public function getJobs() {
    	return $this->jobs;
    }

}
