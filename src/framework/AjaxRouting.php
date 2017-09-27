<?php
/**
 *
 * File: AjaxRouting.php
 *
 * Created by Yiannis Kiranis <rocean74@gmail.com>
 * http://www.apps4net.eu
 *
 * Date: 27/09/2017
 * Time: 12:29
 *
 * Κάνει το routing για τις ajax κλήσεις
 *
 */

namespace apps4net\framework;

class AjaxRouting
{
    protected $controller = 'Ajax';
    protected $method = 'index';
    protected $params = [];


    public function __construct()
    {
        $url = $this->parseUrl();

        $appAjax = 'apps4net\\parrot\\app\\Ajax';
        $frameworkAjax = 'apps4net\\framework\\Ajax';

//        trigger_error($url[0] . ' ' . $url[1]);
        if($url[0]=='app') {
            $controller = $appAjax;
        } else {
            $controller = $frameworkAjax;
        }

        $method = $url[1];

        trigger_error($controller);

        if(class_exists($controller)) {
            $cont = new $controller();
            $cont->$method();
        }
    }

    public function parseUrl()
    {
        if(isset($_GET['url']))
        {
            trigger_error($_GET['url']);
            return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
    }
}