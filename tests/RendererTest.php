<?php

namespace ReactJsBridge;

class RendererTest extends \PHPUnit_Framework_TestCase
{
    public function getRenderer()
    {
        return new Renderer(array(
            'react_path' => __DIR__ . '/../vendor/bower/react',
            'app_paths' => array(
                __DIR__ . '/components'
            )
        ));
    }

    public function testInit()
    {
        $renderer = $this->getRenderer();
        $this->assertSame('object', $renderer->executeJs('print(typeof React)'));
        $this->assertSame('object', $renderer->executeJs('print(typeof ReactDOM)'));
        $this->assertSame('object', $renderer->executeJs('print(typeof ReactDOMServer)'));
    }

    public function testRenderHelloWorld()
    {
        $renderer = $this->getRenderer();

        // assert component is loaded
        $this->assertSame('function', $renderer->executeJs('print(typeof HelloWorld)'));

        // render component
        $markup = $renderer->getComponentMarkup('HelloWorld');
        $this->assertRegExp('@<div data-reactid="[^"]+" data-react-checksum="[^"]+">Hello world</div>@', $markup);

        // render component with options
        $markup = $renderer->getComponentMarkup('HelloWorld', array('country' => 'Netherlands'));
        $this->assertRegExp('@<div data-reactid="[^"]+" data-react-checksum="[^"]+">Hello Netherlands</div>@', $markup);
    }

    /**
     * @expectedException \V8JsScriptException
     */
    public function testRenderInvalidComponent()
    {
        $renderer = $this->getRenderer();
        $renderer->getComponentMarkup('NotFound');
    }

    public function testAppFile()
    {
        $renderer = new Renderer(array(
            'react_path' => __DIR__ . '/../vendor/bower/react',
            'app_paths' => array(
                __DIR__ . '/components/HelloWorld.js'
            )
        ));

        // assert component is loaded
        $this->assertSame('function', $renderer->executeJs('print(typeof HelloWorld)'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAppPaths()
    {
        $renderer = new Renderer(array(
            'react_path' => __DIR__ . '/../vendor/bower/react',
            'app_paths' => '/tmp/does-not-exist',
        ));
        $renderer->executeJs('print(true)');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAppPath()
    {
        $renderer = new Renderer(array(
            'react_path' => __DIR__ . '/../vendor/bower/react',
            'app_paths' => array(
                '/tmp/does-not-exist'
            )
        ));
        $renderer->executeJs('print(true)');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidReactPath()
    {
        $renderer = new Renderer(array(
            'react_path' => '/tmp/does-not-exist',
        ));
        $renderer->executeJs('print(true)');
    }
}
