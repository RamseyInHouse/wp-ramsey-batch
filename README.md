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