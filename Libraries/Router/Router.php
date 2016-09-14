<?php
namespace Libraries\Router;

class Router{
	private $routes = array();
	private $requestURL = "/";

  public function when($url, $callback){
		$this->routes[$url] = $callback;
	}

	private function argsNamed($url, $regex, $request) {
		$re = "/:[^\\/]*/";
		preg_match($regex, $request, $args);
		unset($args[0]);
		$args = array_values($args);

		preg_match_all($re, $url, $matches);
		$matches = $matches[0];
		$fArgs = [];
		foreach ($matches as $key => $value) {
			$value = str_replace(":", "", $value);
			if(!empty($value))
				$fArgs[$value] = $args[$key];
		}

		return (Object) $fArgs;
	}

	private function validUrlRegex($regex, $request) {
		return empty(preg_replace($regex, '', $request));
	}

	public function regex($url){
		$url = preg_replace("/\::[^\\/]*/", "(.*)", $url);
		$url = preg_replace("/\:[^\\/]*/", "(.*?)", $url);
		$url = "/".str_replace('/', '\/', $url)."/";
		return $url;
	}

	private function urlPattern($url) {
		$url = (substr($url, strlen($url)-1) <> "/") ? $url."/" : $url;
		return $url;
	}

	private function walkRoutes($request){
		foreach ($this->routes as $url => $callback) {
			$url = $this->urlPattern($url);
			$urlRegex = $this->regex($url);

			if($this->validUrlRegex($urlRegex, $request)) {
				$args = $this->argsNamed($url, $urlRegex, $request);
				if(is_callable($callback)) {
					call_user_func($callback, $args);
				//para classes -> funções
				// }else{
				// 	$classMethod = explode("->", $callback);
				// 	$class = new $classMethod[0]();
				// 	$method = $classMethod[1];
				//
				// 	call_user_func_array([$class, $method], [$args]);
				}
				exit;
			}

		}

	}

	public function setRouting($requestURL){
		$this->requestURL = $this->urlPattern($requestURL);
	}

	function __destruct() {
		$this->walkRoutes($this->requestURL);
	}
}
