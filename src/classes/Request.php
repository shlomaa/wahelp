<?php

namespace classes;

class Request {

    private $request = [];
    private $get = [];
    private $post = [];
    private $server = [];
    private $session = [];
    private $cookie = [];
    private $query = [];
    private $payload = '';

    public function __construct() {
        $this->request = $_REQUEST;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->session = $_SESSION;
        $this->cookie = $_COOKIE;
        $this->payload = file_get_contents('php://input');
    }

    public function data() {

        return $this->payload;
    }

    public function getGet($param = NULL) {

        return $this->getParam('get', $param);
    }

    public function getQuery($param = NULL) {

        return $this->getParam('query', $param);
    }

    public function getPost($param = NULL) {

        return $this->getParam('post', $param);
    }

    public function runRoute() {
        $request_data = $this->parseRequest();
        $class = '\controllers\\' . ucfirst($request_data[0]);
        $method = 'action' . ucfirst($request_data[1]);

        if (class_exists($class)) {
            $controller = new $class();
            if (in_array($method, get_class_methods($controller))) {
                $this->query = $request_data[3];
                ob_start();
                print $controller->$method($this, $request_data[2]);

                return ob_get_clean();
            }
        }

        throw new \Exception('controller ' . $class . '::' . $method . ' not found');
    }

    private function parseRequest() {

        $uri = parse_url($this->server['REQUEST_URI']);
        $uri_parts = explode('/', $uri['path']);
        $query = [];
        parse_str($uri['query'], $query);

        array_shift($uri_parts);
        $controller = array_shift($uri_parts);
        $action = array_shift($uri_parts);
        $action = !empty($action) ? $action : 'index';
        $arguments = $uri_parts;

        return [
            $controller,
            $action,
            $arguments,
            $query,
        ];
    }

    private function getParam($method, $param = NULL) {

        return !empty($param)
            ? isset($this->{$method}[$param]) ? $this->{$method}[$param]: ''
            : $this->$method;
    }

}