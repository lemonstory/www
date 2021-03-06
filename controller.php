<?php
require dirname(__FILE__).'/init.php';

## log page runtime
define('SLOWPAGELOG', true);
define('SLOWPAGELOGPATH', '/alidata1/www/logs/');
define('SLOWPAGELOGTIME', '0.5');
Runtime::logRunTime();
register_shutdown_function(array('Runtime', 'logRunTime'));

abstract class controller
{
    protected $actionData = array();
    
    public function __construct()
    {
        //$this->checkHostInfo();
        $this->getActionData();
        $this->checkFilters();
        //$this->getAppVertion();
        $this->action();
    }
    
    protected function checkHostInfo()
    {
        $host = $_SERVER['HTTP_HOST'];
        if($host != 'www.xiaoningmeng.net') {
            header("HTTP/1.0 404 Not Found");
            exit;
        }
    }
    
    protected function getAppVertion()
    {
    	$userAgent = @$_SERVER['HTTP_USER_AGENT'];
    	if($userAgent!="")
    	{
	    	$agentArr = explode('/', $userAgent);
	    	$version = str_pad(str_replace('.', '', @$agentArr[1]),9,0)+0;
	    	if($version==0)
	    	{
	    		$version=1610000;
	    	}
	    	if($version>0)
	    	{
	    		$_SERVER['visitorappversion'] = $version;
	    	}
    	}
    	if(isset($_GET['visitorappversion']))
    	{
    		$version = str_pad(str_replace('.', '', @$_GET['visitorappversion']),9,0)+0;
    		$_SERVER['visitorappversion'] = $version;
    	}
    }
    
    
    protected function getSmartyObj()
    {
        include_once SERVER_ROOT.'libs/smarty/Smarty.class.php';
        $smarty					 	= new Smarty();
        $smarty->template_dir   	= SERVER_ROOT."view/html/";
        $smarty->compile_dir 		= SERVER_ROOT."view/templates_c/";
        $smarty->cache_dir   		= SERVER_ROOT."view/cache/";
        return $smarty;
    }
    
    public function checkFilters()
    {
        if (HTTP_CACHE == true) {
            $this->checkHttpCache();
        }
    }
    
    public function filters()
    {
        return array();
    }
    
    private function getActionData()
    {
        $script = str_replace('.php', '', @$_SERVER['SCRIPT_NAME']);
        $scriptArr = @explode('/', trim($script, '/'));
        if (!is_array($scriptArr)){
            return array();
        }
        @list($module, $action) = $scriptArr;
        $data['module'] = $module;
        $data['action'] = $action;
        
        $querys = $_SERVER["QUERY_STRING"];
        $params = array();
        if (!empty($querys)){
            $queryParts = explode('&', $querys);
            foreach ($queryParts as $param)
            {
            	if($param=="")
            	{
            		continue;
            	}
                $item = explode('=', $param);
                $params[$item[0]] = $item[1];
            }
        }
        $data['params'] = $params;
        
        $this->actionData = $data;
        
        return $data;
    }

    protected function getUid()
    {
        $SsoObj = new Sso();
        $uid = $SsoObj->getUid();
        return $uid;
    }
    
    
    abstract  function action();
    
    
    protected function showErrorJson($data=array())
    {
        if(empty($data))
        {
            $data = ErrorConf::systemError();
        }
        echo json_encode($data);
        exit;
    }
    protected function showSuccJson($data=array())
    {
        if(empty($data) && $data!=0)
        {
            echo json_encode(array('code'=>10000));
        }else{
            echo json_encode(array('code'=>10000,'data'=>$data));
        }
        exit;
    }
    
    public function getRequest($option, $default='', $method='request')
    {
        if ($method == 'get'){
            return isset($_GET[$option]) ? $_GET[$option] : $default;
        } else if ($method == 'post'){
            return isset($_POST[$option]) ? $_POST[$option] : $default;
        } else{
            return isset($_REQUEST[$option]) ? $_REQUEST[$option] : $default;
        } 
    }
    
    protected function redirect($url, $statusCode = 302)
    {
        if(strpos($url,'/')===0 && strpos($url,'//')!==0) {
            if(isset($_SERVER['HTTP_HOST'])) {
                $hostInfo = 'http://'.$_SERVER['HTTP_HOST'];
            } else {
                $hostInfo = 'http://'.$_SERVER['SERVER_NAME'];
            }
            $url = $hostInfo . $url;
        }
        header('Location: ' . $url, true, $statusCode);
    }
    
    public function checkHttpCache()
    {
        $httpCacheObj = new HttpCache();
        $httpCacheObj->checkHttpCache($this->actionData);
    }
    
    public function commonHumanTime($time)
    {
		$dur = time() - $time;
		if ($dur < 60) {
			return $dur.$_SERVER['morelanguage']['sec'];
		} elseif ($dur < 3600) {
			return floor ( $dur / 60 ) . $_SERVER['morelanguage']['mins'];
		} elseif ($time > mktime ( 0, 0, 0 )) {
			return $_SERVER['morelanguage']['today'] . date ( 'H:i', $time );
		} elseif ($time > mktime ( 0, 0, 0 )-86400) {
			return $_SERVER['morelanguage']['yesterday'] . date ( 'H:i', $time );
		} elseif ($time > mktime ( 0, 0, 0 )-172800 ){
			return $_SERVER['morelanguage']['tfyesterday'] . date ( 'H:i', $time );
		}elseif ($time > mktime ( 0, 0, 0)-86400*365){
			return date ( 'm-d H:i', $time );
		}else {
			return date ( 'Y-m-d', $time );
		}
    }
}