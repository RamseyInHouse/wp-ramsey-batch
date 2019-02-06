<?php
namespace RamseySolutions\RamseyBatch;

abstract class BatchJob
{
    public $items;
    public $progress;
    protected $lastRunTimes;

    /**
     * Set batch items
     * @param mixed $items Batch items
     */
    abstract protected function setItems($items);

    /**
     * Run batch items
     * @return mixed
     */
    abstract public function run();

    /**
     * Run a single batch item
     * @return mixed
     */
    abstract public function runItem();

    /**
     * Register batch job
     * @param  array  $jobs Array of registered batch jobs
     * @return array
     */
    abstract public function registerBatchJob(array $jobs);

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->items = [];
        $this->lastRunTimes = get_option(RB_PLUGIN_SLUG . '_batch-run-timestamps', []);
    }

    /**
     * Get batch items
     * @return obj|array Iterable object or array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Check if batch items are available
     */
    public function hasItems()
    {
        return !empty($this->getItems());
    }

    /**
     * Check if the batch name is allowed to run based on the instantiated class.
     * @return boolean
     */
    public function isAllowedBatch()
    {
        if (empty($_REQUEST) || !array_key_exists('batchName', $_REQUEST)) {
            return false;
        }

        return stripslashes($_REQUEST['batchName']) == get_class($this);
    }

    /**
     * Get the timestamp corresponding to the time this particular batch was last run.
     * @return float Timestamp
     */
    public function getLastRunTime()
    {
        if (empty($this->lastRunTimes) || !array_key_exists(get_called_class(), $this->lastRunTimes)) {
            return '';
        }

        return $this->lastRunTimes[get_called_class()];
    }

    /**
     * Get the formatted date corresponding to the time this particular batch was last run.
     * @param  string $format  PHP date format
     * @param  string $default Default string if batch has never been run
     * @return string
     */
    public function getLastRunDate($format = 'M j, Y H:i:s', $default = '--')
    {
        if (!$this->getLastRunTime()) {
            return $default;
        }
        return date($format, $this->getLastRunTime());
    }

    /**
     * Update the timestamp when this batch was last run
     * @param  float $timestamp A PHP timestamp
     * @return bool True if option value has changed, false if not or if update failed.
     */
    protected function updateLastRunDate($timestamp = null)
    {
        if (!$timestamp) {
            $timestamp = current_time('timestamp');
        }
        $this->lastRunTimes[get_called_class()] = $timestamp;
        return update_option(RB_PLUGIN_SLUG . '_batch-run-timestamps', $this->lastRunTimes, false);
    }
}
