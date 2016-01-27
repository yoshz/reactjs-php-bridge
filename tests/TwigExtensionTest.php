<?php

namespace ReactJsBridge;

class TwigExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Twig_Environment */
    private $twig;

    public function setUp()
    {
        $reactjs = new Renderer(array(
            'react_path' => __DIR__ . '/../vendor/bower/react',
            'app_paths' => array(
                __DIR__ . '/components'
            )
        ));
        $ext = new TwigExtension($reactjs);
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/templates');
        $this->twig = new \Twig_Environment($loader);
        $this->twig->addExtension($ext);
    }

    public function testRenderHelloWorld()
    {
        $template = $this->twig->loadTemplate('helloworld.html.twig');
        $markup = $template->render(array());
        $this->assertRegExp('@<div id="[^"]+"><div data-reactid="[^"]+" data-react-checksum="[^"]+">Hello world</div></div>@', $markup);
    }

    public function testRenderInit()
    {
        $template = $this->twig->loadTemplate('helloworld.html.twig');
        $this->assertInternalType('string', $template->render(array()));

        $template = $this->twig->loadTemplate('footer.html.twig');
        $markup = $template->render(array());
        $this->assertRegExp("@^<script>ReactDOM.render\(React.createElement\(HelloWorld, \[\]\), document.getElementById\('[^']+'\)\)</script>$@", trim($markup));
    }

    public function testRenderEmpty()
    {
        $template = $this->twig->loadTemplate('footer.html.twig');
        $markup = $template->render(array());
        $this->assertSame("", trim($markup));
    }
}
