<?php
namespace RamseySolutions\RamseyBatch\Views;

class BatchView extends AdminPage {
	
	public function __construct(object $controller, string $slug, string $title) {
		parent::__construct($controller, $slug, $title);
	}

	public function display() {
		echo $this->open();
		?>
		<div class="notice notice-warning">
		<p>The batch jobs here are powerful <strong>and must be run with caution</strong>. It's likely that they will irreversibly change your data. Carefully read and understand each job's description before proceeding.</p>
		</div>

		<table class="wp-list-table widefat fixed striped">
            <thead>
                <th id="name" class="manage-column">Batch Name</th>
                <th id="description" class="manage-column">Job Description</th>
                <th id="lastRun" class="manage-column">Date Last Run</th>
                <th id="actions" class="manage-column">Run Job</th>
            </thead>
            <tbody>
            <?php echo $this->listBatchJobs(); ?>
            </tbody>
        </table>
		<?php
		// echo $this->debug();
		echo $this->close();
	}

	/**
     * List batch jobs
     * @return string
     */
    protected function listBatchJobs() {
        $jobs = $this->controller->getJobs();
        ob_start();
        
        if( empty($jobs) ) {
            echo '<tr><td colspan="4">There are no registered batch jobs.</td></tr>';
            return ob_get_clean();
        }

        foreach( $jobs as $key => $job ) {
            echo "<tr>";
            echo '<td class="name column-title">' . $job['name'] . '</td>';
            echo '<td class="description">' . $job['description'] . '</td>';
            echo '<td class="lastRun column-date">' . $job['lastRunDate'] . '</td>';
            echo '<td class="actions"><button name="batchJobTrigger" type="button" class="button"  data-batch-name="' . $key . '">Run Now</button></td>';
            echo "</tr>";
            echo '<tr class="progressMeter" data-batch-name="' . stripslashes($key) . '" style="display:none;">';
                echo '<td colspan="4">';
                echo '<p class="status">Starting job&hellip;</p>';
                echo '<div class="progress" style="border:1px solid #efefef;border-radius:3px;"><span class="meter" style="width:0%;height:14px;background-color:#009cb3;display:block;"></span></div>';
                echo '</td>';
            echo "</tr>";
        }
        return ob_get_clean();
    }

}