<?php

namespace Armor\HandlingTools;

require "RequestQueryParameters.php";
require "RequestPath.php";

class Request {
    public $path, $_query;
    private $method;

    public function __construct($method, $path, $path_params=array(), $query_params=array())
    {
        $this->method = $method;
        $this->path = new RequestPath($path, $path_params);
        $this->_query = new RequestQueryParameters($query_params);
    }

    public function __get($name)
    {
        if ($name == 'query') {
            if ($this->method == 'get')
                return $this->_query;
            else
                exit('Method doesn\'t have query parameters');
        } elseif ($name == 'body') {
            if ($this->method == 'post')
                return $this->_query;
            else
                exit('Method doesn\'t have a request body');
        }
    }
}