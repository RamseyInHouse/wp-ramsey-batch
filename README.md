# WordPress Batch Processing

Provides a framework to handle large data processing jobs by breaking it into smaller chunks and running each job individually via AJAX requests.

## Installation

The recommended method of installation is via [Composer](https://getcomposer.org/).

### Composer
For more information on using Composer to manage WordPress plugins [read this guide](https://deliciousbrains.com/using-composer-manage-wordpress-themes-plugins/).

#### Add the repository to composer.json

1. In the `extra` array ensure you have:

```js
"installer-paths": {
  "content/plugins/{$name}/": ["type:wordpress-plugin"]
}
```

Be sure that the installer path reflects your WordPress plugin directory.


2. In the `repositories` array of composer.json, add the following

```js
{
  "type": "git",
  "url": "git@github.com:RamseyInHouse/wp-ramsey-batch.git"
}
```

3. In the `require` object add:

```js
"RamseyInHouse/wp-ramsey-batch": "^1.0"
```
If you'd like a different version of the plugin, check the [Releases](https://github.com/RamseyInHouse/wp-ramsey-batch/releases) section of Github. This plugin adheres to [semantic versioning guidelines](https://getcomposer.org/doc/articles/versions.md).

4. Run the `composer install` command.

### Download and Install

You can download the plugin files here and add them to the `plugins` directory of your WordPress installation. [Follow the directions here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation_by_FTP).

## Creating a Batch Job

Before starting be sure that the "Ramsey Batch" plugin is activated.

### Include your class in your theme/plugin

Current convention is to have your batch jobs live inside your theme. This could easily be extended to have batch jobs live inside other plugins. Regardless, you need to tell WordPress to include the correct files.

In your theme's `functions.php` file add:

```php
if( is_admin() && class_exists('RamseySolutions\RamseyBatch\Controllers\BatchController')) {
  RamseySolutions\RamseyBatch\Controllers\BatchController::register('MyAppNamespace\Batch\MyBatchJob');
}
```

### Create a batch job class and register the job
Begin by creating a class for your batch job with a basic `__construct` method. Your class _must_ extend the `\RamseySolutions\RamseyBatch\BatchJob` class.

The filter `ramsey-batch-jobs` allows you to hook a class method to register your batch job into the UI.

```php
<?php
namespace MyAppNamespace\Batch;

class MyBatchJob extends \RamseySolutions\RamseyBatch\BatchJob {

	/**
	 * Items to be processed one-by-one
	 * @var array
	 */
	protected $items = [];

	/**
	 * Constructor method. Must always call the parent constructor. Should register the batch job into the UI using the 'ramsey-batch-jobs' filter.
	 */
	public function __construct() {
		parent::__construct();

		add_filter('ramsey-batch-jobs', [$this, 'registerBatchJob']);
	}

	/**
	 * Register the batch job
	 * @param  array  $jobs Array of batch job information
	 * @return array
	 */
	public function registerBatchJob(array $jobs) {
		$jobs[__CLASS__] = [
			'name' => 'My Batch Job Name',
			'description' => 'Description of what the batch job does.',
			'lastRunDate' => $this->getLastRunDate()
		];

		return $jobs;
	}
}
```

There are a few methods that your class must provide. The general flow of plugin is:

1. **run()** method is called - This should update the last run date and compile your list of items to be batch processed one-by-one. It should output a JSON array of items if successful.
1. **runItem()** method is called for each item in your collection. Each item from the JSON array is passed to the method individually for processing. If the item is processed successfully, it should [output a successful response](https://codex.wordpress.org/Function_Reference/wp_send_json_success) via JSON.


The required methods are stubbed out below:

```php
/**
 * Set the batch job items
 * @param array $items Array of values to process over. These values will be passed into your processing function one-by-one via AJAX. Use the 'run' method to compile
 */
protected function setItems($items) {
	$this->items = $items;
}

/**
 * Start the batch job by compiling the batch
 * @return string JSON string
 */
public function run() {
	
	//Update the last run date to be displayed in the UI
	$this->updateLastRunDate();

	//Get a large batch of something, posts, users, DB records, etc. For this example we get all the 'post' post type ID's in a numerically indexed array.
	global $wpdb;
	$postIds = $wpdb->get_results("SELECT ID from {$wpdb->prefix}posts WHERE post_type = 'post'", ARRAY_N);

	//Set the items for later reference
	$this->setItems($postIds);

	//Output JSON for AJAX
	wp_send_json_success([
		'items' => $this->getItems()
	]);
}

/**
 * Process an individual batch job item
 * @return array Response data
 */
public function runItem() {
	if( !wp_doing_ajax() ) {
		return;//You should probably log and throw an error.
	}

	//Grab our item from the AJAX request
	$postId = $_REQUEST['item'];

	//Setup an empty array to hold our response. As batch jobs should only be run in the WP admin by authenticated users, you can use this response to output information in the browser console.
	$response = [];

	//We're only expecting post ID's, so let's do a quick sanity check. If this fails in real life, you should probably log it out for further analysis.
	if( !$postId || !is_numeric($postId) ) {
		$response['reason'] = __FUNCTION__ . ' requires a numeric post ID.';
		wp_send_json_error($response);
	}

	$response['postId'] = $postId;

	//Let's add some post meta for each post
	$meta_key = 'batch_meta_key';
	$meta_value = 'Your batch meta value';
	$postMetaId = add_post_meta($postId, $meta_key, $meta_value);

	if( !$postMetaId ) {
		$response['reason'] = 'Could not save post meta.';
		wp_send_json_error($response);
	}

	$response['postMetaId'] = $$postMetaId;
	$response['reason'] = "Post meta for $postId was updated to '$meta_value'.";

	//Send the JSON success message
	wp_send_json_success($response);
}
```

### The full example code

A full copy of the code is shown below.

```php
<?php
namespace MyAppNamespace\Batch;

class MyBatchJob extends \RamseySolutions\RamseyBatch\BatchJob {

	/**
	 * Items to be processed one-by-one
	 * @var array
	 */
	protected $items = [];

	/**
	 * Constructor method. Must always call the parent constructor. Should register the batch job into the UI using the 'ramsey-batch-jobs' filter.
	 */
	public function __construct() {
		parent::__construct();

		add_filter('ramsey-batch-jobs', [$this, 'registerBatchJob']);
	}

	/**
	 * Register the batch job
	 * @param  array  $jobs Array of batch job information
	 * @return array
	 */
	public function registerBatchJob(array $jobs) {
		$jobs[__CLASS__] = [
			'name' => 'My Batch Job Name',
			'description' => 'Description of what the batch job does.',
			'lastRunDate' => $this->getLastRunDate()
		];

		return $jobs;
	}

	/**
	 * Set the batch job items
	 * @param array $items Array of values to process over. These values will be passed into your processing function one-by-one via AJAX. Use the 'run' method to compile
	 */
	protected function setItems($items) {
		$this->items = $items;
	}

	/**
	 * Start the batch job by compiling the batch
	 * @return string JSON string
	 */
	public function run() {
		
		//Update the last run date to be displayed in the UI
		$this->updateLastRunDate();

		//Get a large batch of something, posts, users, DB records, etc. For this example we get all the 'post' post type ID's in a numerically indexed array.
		global $wpdb;
		$postIds = $wpdb->get_results("SELECT ID from {$wpdb->prefix}posts WHERE post_type = 'post'", ARRAY_N);

		//Set the items for later reference
		$this->setItems($postIds);

		//Output JSON for AJAX
		wp_send_json_success([
			'items' => $this->getItems()
		]);
	}

	/**
	 * Process an individual batch job item
	 * @return array Response data
	 */
	public function runItem() {
		if( !wp_doing_ajax() ) {
			return;//You should probably log and throw an error.
		}

		//Grab our item from the AJAX request
		$postId = $_REQUEST['item'];

		//Setup an empty array to hold our response. As batch jobs should only be run in the WP admin by authenticated users, you can use this response to output information in the browser console.
		$response = [];

		//We're only expecting post ID's, so let's do a quick sanity check. If this fails in real life, you should probably log it out for further analysis.
		if( !$postId || !is_numeric($postId) ) {
			$response['reason'] = __FUNCTION__ . ' requires a numeric post ID.';
			wp_send_json_error($response);
		}

		$response['postId'] = $postId;

		//Let's add some post meta for each post
		$meta_key = 'batch_meta_key';
		$meta_value = 'Your batch meta value';
		$postMetaId = add_post_meta($postId, $meta_key, $meta_value);

		if( !$postMetaId ) {
			$response['reason'] = 'Could not save post meta.';
			wp_send_json_error($response);
		}

		$response['postMetaId'] = $$postMetaId;
		$response['reason'] = "Post meta for $postId was updated to '$meta_value'.";

		//Send the JSON success message
		wp_send_json_success($response);
	}
}