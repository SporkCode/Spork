Spork\CSS
=========

The CSS package provides tools to integrate CSS preprocessors into a ZF2
application. It includes compiler classes for [Stylus](http://learnboost.github.io/stylus/),
[Less](http://lesscss.org/), [Sass](http://sass-lang.com/) and is extendible to
support addition preprocessors. It also includes an event listen to trigger
updating CSS files automatically.

Compilers
---------

### Configuration

Compiler instances can easily be created and configured via a Service Manager
and application configuration. The Spork Module configuration sets up services
for each compiler class by default.

config/module.config.php
```
	'service_manager' => array(
		'factories' => array(
			'cssLess' => 'Spork\CSS\Less',
			'cssSass' => 'Spork\CSS\Sass',
			'cssStylus' => 'Spork\CSS\Stylus',
		),
	),
```

Each compiler instance created by the Service Manager looks for configuration
options in the application configuration.

Sample configuration
```
	'css-stylus' => array(
		'arguments' => array(),
		'cache' => CACHE_SERVICE_NAME,
		'compiler' => PATH_TO_EXECUTABLE,
		'compress' => TRUE | FALSE,
		'extensions' => array('styl'),
		'includes' => array(PATH_TO_INCLUDE, ...),
	),
	'css-less' => array(
		...
	),
	'css-sass' => array(
		...
	),
```

### Compiling

The compile() function converts source files into CSS

```
public function compile($source, $destination = null, $include = null)
```

*$source* specifies the source file or directory path to process

*$destination* specifies the target file or directory. If $source is a directory
destination must also be a directory. Source and destination can be the same
directory. If no destination is specified the CSS code is returned as a string.

*$include* specifies directory(s) to search for include files

### Caching

The compiler will cache results when returning CSS as a string automatically 
when a cache has been configured. This is most useful when used in with the 
[CSS View Helpers](../View/Helper/CSS/README.md).

To configure a cache include the service name of an 
Zend\Cache\Storage\Adapter\AbstractAdapter instance in the compilers 
configuration. See example above. You can also enable caching by calling the 
setCache() function.

Notes:
 - Cache has no effect when a destination file or directory is specified.
 - The cache will not refresh automatically when the source is updated and must
 be flushed.

### Extending

Compiler classes extend AbstractCompiler and must implement the abstract function
getCommandArguments() which takes the compiler options and creates command line
arguments.

Event Listener
--------------

The UpdateListener class is an event listener which checks source files and 
updates the CSS when it is out of date.

### Configuration

**Warning**
It is not recommended to use the update listen on production environments. An 
easy way to setup the listener in the development environment is to put the
configuration in the application's local configuration file.

Sample Configuration
```
	'css-update' => array(
		'compiler' => 'cssStylus',
		'builds' => array(
			array(
				'source' => 'path/to/source',
				'destination' => 'path/to/destination',
				'includes' => array('path/to/include') // optional
				'compress' => true // optional
			),
		),
	),
	'service_manager' => array(
		'invokables' => array(
			'cssStylus' => 'Spork\CSS\Stylus',
			'cssUpdateListener' => 'Spork\CSS\UpdateListener', 
		),
	),
	'listeners' => array(
		'cssUpdateListener',
	),
```