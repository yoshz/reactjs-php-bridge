ReactJs PHP Bridge
==================

This library integrates [react.js](https://facebook.github.io/react/) in PHP using the [v8js](https://github.com/phpv8/v8js) extension to allow server-side rendering.
It also contains a Twig extension to embed react component rendering inside Twig templates.

Prerequisites
=============

You need the [V8Js PHP extension](http://php.net/v8js) to run this library.

To install this extension follow the official installation guides:
* [On Linux](https://github.com/preillyme/v8js/blob/master/README.Linux.md)
* [On MacOS](https://github.com/preillyme/v8js/blob/master/README.MacOS.md)
* [On Windows](https://github.com/preillyme/v8js/blob/master/README.Win32.md)

You can also use one of my Docker images:
* [php-fpm-v8js](https://hub.docker.com/r/yoshz/php-fpm-v8js/)
* [php-fpm-v8js-dev](https://hub.docker.com/r/yoshz/php-fpm-v8js-dev/)

Installation
============

* Require package the package in your project:
```bash
    composer require yoshz/reactjs-php-bridge
```

* Install react.js using bower:
```bash
    bower install --save react    
```

Usage
=====

Using the renderer directly:
```php
$reactjs = new \ReactJsBridge\Renderer(array(
    'react_path' => __DIR__ . /bower_components/react',
    'app_paths' => array(
        __DIR__ . '/app'
    )
));
?>
<html>
<head>
    <script src="bower_components/react/react.min.js"></script>
    <script src="bower_components/react/react-dom.min.js"></script>
</head>
<body>
    <main>
        <?= $reactjs->getComponentMarkup('HelloWorld') ?>
    </main>

    // initialize client-side javascript
    <script><?= $reactjs->getClientJs() ?></script>
</body>
</html>
```

Or using the Twig extension:
```php
$reactjs = new \ReactJsBridge\Renderer(array(
    'react_path' => __DIR__ . /bower_components/react',
    'app_paths' => array(
        __DIR__ . '/components'
    )
));
$ext = new \ReactJsBridge\TwigExtension($reactjs);
$loader = new \Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new \Twig_Environment($loader);
$twig->addExtension($ext);

echo $twig->loadTemplate('helloworld.html.twig')->render();
```

And the Twig file `templates/helloworld.html.twig`:
```twig
<html>
<head>
    <script src="bower_components/react/react.min.js"></script>
    <script src="bower_components/react/react-dom.min.js"></script>
</head>
<body>
    <main>
        {{ reactjs_component('HelloWorld') }}
    </main>
    {{ reactjs_clientjs() }}
</body>
</html>
```

Credits
=======

This library is inspired by the [reactjs/react-php-v8js](https://github.com/reactjs/react-php-v8js) library.
