Spork\View\Helper\CSS
=====================

The CSS view helpers provides an easy way to leverage the advantages of a CSS 
preprocessor for page specific design without increasing load time overhead 
from loading additional style sheets or adding unnecessary bulk to a global 
style sheet.  

Each view helper generates inline style sheets by converting a CSS 
preprocessor script such as [Stylus](http://learnboost.github.io/stylus/), 
[Less](http://lesscss.org/) and [Sass](http://sass-lang.com/) into CSS using
[Spork\CSS](../../../CSS/README.md) and wrapping it in a \<script\> tag. 
They use a Zend\View\Resolver to locate script files and by default mimic the
view renderer's resolver strategy so CSS preprocessor scripts can be organized
along with view templates.

Configuration
-------------

The Spork module configuration adds the CSS view helpers to the view helper
plugin manager by default.

config/module.config.php
```
    'view_helpers' => array(
        'invokables' => array(
            'cssLess' => 'Spork\View\Helper\CSS\Less',
            'cssSass' => 'Spork\View\Helper\CSS\Sass',
            'cssStylus' => 'Spork\View\Helper\CSS\Stylus',
        ),
    ),
```

By default each view helper expects to find a Spork\CSS compiler in the service
manager. This is also configured by default in the Spork module configuration.
The compiler can also be configured using the application configuration.
See [CSS\README.md](../../../CSS/README.md) for more details.

Use
---

In a view template you can invoke the view helper with a template string to
generate the inline style.

```
<?php
	echo $this->cssStylus('index/page');
?>
```

This will look for 'index/page' using the same resolver strategy as the view
renderer, compile the file into CSS and return it wrapped in a \<style\> tag. If 
the resolver strategy includes a TemplatePathStack it will use the appropriate 
extension to resolve the file ('index/page.styl').

The default compiler and resolver and also be overridden.

```
<?php
	$this->cssStylus()->setCompiler('myCssService')->setResolver($myResolver);
	echo $this->cssStylus();
	
	// OR
	
	echo $this->cssStylus()->setCompiler('myCssService')->setResolver($myResolver)->compile();
?>
```

The compiler can be the name of service manager service or an instance 
implementing Spork\CSS\AbstractCompiler.

Caching
-------

It is highly recommended to configure the compiler to cache results in a 
production environment. See [CSS\README.md](../../../CSS/README.md) for more details.