<?php

namespace ReactJsBridge;

class TwigExtension extends \Twig_Extension
{
    /** @var Renderer */
    private $renderer;


    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getName()
    {
        return 'reactjs';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('reactjs_component', array($this, 'renderComponent'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('reactjs_init', array($this, 'renderInit'), array('is_safe' => array('html')))
        );
    }

    public function renderComponent($name, array $options = array(), array $attrs = array())
    {
        return $this->renderer->getComponentMarkup($name, $options);
        $instanceId = $this->reactjs->registerComponent($name, $options);

        $attrs['id'] = $instanceId;
        array_walk($attrs, function(&$value, $attr) {
            $value = $attr . '="' . htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8') . '"';
        });

        return '<div ' . implode(' ', $attrs) . '>' . $this->reactjs->getComponentMarkup($instanceId) . '</div>';
    }

    public function renderInit()
    {
        $js = $this->renderer->getClientJs();
        if ($js) {
            return '<script>' . $js . '</script>' . PHP_EOL;
        }

        return '';
    }
}
