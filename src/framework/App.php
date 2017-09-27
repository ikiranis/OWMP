<?php
/**
 *
 * File: App.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 27/09/2017
 * Time: 12:29
 *
 */

namespace apps4net\framework;

class App
{
    protected $controller = 'Ajax';
    protected $method = 'index';
    protected $params = [];


    public function __construct()
    {
        $url = $this->parseUrl();
        $controller = 'apps4net\\framework\\'.$url[0];
        $method = $url[1];

        if(class_exists($controller)) {
            $cont = new $controller();
            $cont->$method();
        }
    }

    public function parseUrl()
    {
        if(isset($_GET['url']))
        {
            return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}