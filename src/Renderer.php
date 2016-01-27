<?php

namespace ReactJsBridge;

class Renderer
{
    /** @var string */
    private $reactPath;

    /** @var array */
    private $appPaths = array();

    /** @var \V8Js */
    private $v8;

    /** @var array */
    private $instances = array();

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'react_path':
                    $this->reactPath = $value;
                    break;

                case 'app_paths':
                    if (!is_array($value)) {
                        throw new \InvalidArgumentException('Option \'app_paths\' is not of type array');
                    }
                    $this->appPaths = $value;
                    break;

                default:
                    throw new \InvalidArgumentException('Unknown option: ' . $key);
            }
        }
    }

    /**
     * Register a component instance
     *
     * @param  string $name Name of component
     * @param  array  $data Array of options to passthrough
     * @return string       Markup
     */
    public function getComponentMarkup($name, array $data = array(), array $attr = array())
    {
        $id = uniqid('react-');
        $this->instances[$id] = array($name, json_encode($data));

        $attr['id'] = $id;
        array_walk($attr, function(&$value, $key) {
            $value = $key . '="' . htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8') . '"';
        });

        return '<div ' . implode(' ', $attr) . '>' . 
            $this->executeJS(sprintf(
                "print(ReactDOMServer.renderToString(React.createElement(%s, %s)))",
                $this->instances[$id][0],
                $this->instances[$id][1]
            )) .
            '</div>';
    }

    /**
     * Get javascript for client initialization
     *
     * @return string Client javascript code
     */
    public function getClientJs()
    {
        $js = array();
        foreach ($this->instances as $id => $instance) {
            $js[] = sprintf(
                "ReactDOM.render(React.createElement(%s, %s), document.getElementById('%s'))",
                $instance[0],
                $instance[1],
                $id
            );
        }

        return implode(";\n", $js);
    }

    /**
     * Initialize V8Js with ReactJS and components
     */
    private function initV8()
    {
        if ($this->v8 !== null) {
            return;
        }

        $react = array();

        // stubs, react
        $react[] = "var console = {warn: function(){}, error: print}";
        $react[] = "var global = global || this, self = self || this, window = window || this";
        foreach(array('react.min.js', 'react-dom.min.js', 'react-dom-server.min.js') as $reactFile) {
            if (!file_exists($this->reactPath . '/' . $reactFile)) {
                throw new \InvalidArgumentException(sprintf('Could not find %s in: %s', $reactFile, $this->reactPath));
            }
            $react[] = file_get_contents($this->reactPath . '/' . $reactFile);
        }

        $react[] = "var React = global.React, ReactDOM = global.ReactDOM, ReactDOMServer = global.ReactDOMServer";

        // app's components
        foreach ($this->appPaths as $appPath) {
            if (is_file($appPath)) {
                $react[] = file_get_contents($appPath);
            } elseif (is_dir($appPath)) {
                foreach (glob($appPath . '/*.js') as $appFile) {
                    $react[] = file_get_contents($appFile);
                }
            } else {
                throw new \InvalidArgumentException('Invalid app path: ' . $appPath);
            }
        }

        $this->v8 = new \V8Js();
        $this->v8->executeString(implode(";\n", $react));
    }

    /**
     * Executes Javascript using V8Js
     *
     * @param string $js JS code to be executed
     * @return string    The execution response
     */
    public function executeJs($js)
    {
        $this->initV8();
        ob_start();
        try {
            $this->v8->executeString($js);
        } catch (\V8JsScriptException $e) {
            ob_end_clean();
            throw $e;
        }
        return ob_get_clean();
    }
}
