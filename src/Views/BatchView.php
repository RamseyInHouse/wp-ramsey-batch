<?php
namespace RamseySolutions\RamseyBatch\Views;

class BatchView extends AdminPage
{
    public function __construct(object $controller, string $slug, string $title)
    {
        parent::__construct($controller, $slug, $title);

        $this->tableColumns = apply_filters(
            RB_PLUGIN_SLUG . '-table-columns',
            [
                [
                    'id' => 'name',
                    'name' => 'Batch Name',
                    'contentKey' => 'name'
                ],
                [
                    'id' => 'description',
                    'name' => 'Job Description',
                    'contentKey' => 'description'
                ],
                [
                    'id' => 'lastRun',
                    'name' => 'Date Last Run',
                    'contentKey' => 'lastRunDate',
                ]
            ]
        );
    }

    public function display()
    {
        echo $this->open(); ?>
		<div class="notice notice-warning">
		    <p>The batch jobs here are powerful <strong>and must be run with caution</strong>. It's likely that they will irreversibly change your data. Carefully read and understand each job's description before proceeding.</p>
		</div>

		<table class="wp-list-table widefat fixed striped">
            <thead>
                <?php foreach ($this->tableColumns as $column): ?>
                    <th id="<?php echo $column['id']; ?>" class="manage-column">
                        <?php echo $column['name']; ?>
                    </th>
                <?php endforeach; ?>
                <th id="actions" class="manage-column">
                    Run Job
                </th>
            </thead>
            <tbody>
                <?php echo $this->listBatchJobs(); ?>
            </tbody>
        </table>
		<?php echo $this->close();
    }

    /**
     * List batch jobs
     * @return string
     */
    protected function listBatchJobs()
    {
        $jobs = $this->controller->getJobs();

        ob_start();

        if (empty($jobs)) {
            echo '<tr><td colspan="4">There are no registered batch jobs.</td></tr>';
            return ob_get_clean();
        }

        foreach ($jobs as $key => $job): ?>

            <tr>
                <?php foreach ($this->tableColumns as $column): ?>
                    <td>
                        <?php echo $job[$column['contentKey']]; ?>
                    </td>
                <?php endforeach; ?>
                <td class="actions">
                    <button name="batchJobTrigger" type="button" class="button" data-batch-name="<?php echo $key; ?>">
                        Run
                    </button>
                </td>
            </tr>

            <tr class="progressMeter" data-batch-name="<?php echo stripslashes($key); ?>" style="display:none;">
                <td colspan="4">
                <p class="status">Starting job&hellip;</p>
                <div class="progress" style="border:1px solid #efefef;border-radius:3px;"><span class="meter" style="width:0%;height:14px;background-color:#009cb3;display:block;"></span></div>
                </td>
            </tr>

        <?php endforeach;
        return ob_get_clean();
    }
}
