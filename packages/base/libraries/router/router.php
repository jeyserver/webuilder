<?php
namespace packages\base;
include('exceptions.php');
use \packages\base\http;
use \packages\base\process;
use \packages\base\options;
use \packages\base\translator\InvalidLangCode;
class router{
	static private $rules = array();
	static public function add($rule, $controller, $method){
		$method = strtolower($method);
		if(in_array($method, array('','post','get','put','delete'))){
			if(is_string($rule)){
				$rule = explode('/', $rule);
			}
			if(is_array($rule)){
				$len = count($rule);
				$parts = array();
				for($x = 0;$x!=$len;$x++){
					$part = $rule[$x];
					if($part){
						$parts[] = self::validPart($part);
					}
				}
				if($parts){
					self::$rules[] = array(
						'path' => $parts,
						'method' => $method,
						'controller' => $controller
					);
					return true;
				}
			}else{
				throw new routerRule($rule);
			}
		}else{
			throw new routerMethod($method);
		}
	}
	static private function validPart($part){
		if(is_string($part) or is_numeric($part)){
			return array(
				'type' => 'static',
				'name' => $part,
			);
		}elseif(is_array($part)){
			if(isset($part['name'])){
				if(!isset($part['type'])){
					if(isset($part['regex'])){
						$part['type'] = 'dynamic';
					}
				}
				if(isset($part['type']) and in_array($part['type'], array('static', 'dynamic'), true)){
					if($part['type'] == 'dynamic'){
						if(isset($part['regex'])){
							if(@preg_match($part['regex'], null) === false){
								throw new routerRulePart($part, "regex is invalid");
							}
						}
					}
					$valid = array(
						'type' => $part['type'],
						'name' => $part['name'],
					);
					if($part['type'] == 'dynamic' and isset($part['regex'])){
						$valid['regex'] = $part['regex'];
					}
					return $valid;
				}else{
					throw new routerRulePart($part, "type is not static or dynamic");
				}
			}else{
				throw new routerRulePart($part, "name is not assigned");
			}
		}else{
			throw new routerRulePart($part);
		}
	}
	private static function CheckShortLang($lang){
		$type = options::get('packages.base.translator.changelang.type');
		if($type == 'short'){
			if(translator::is_shortCode($lang)){
				$langs = translator::getAvailableLangs();
				foreach($langs as $l){
					if(substr($l, 0, 2) == $lang){
						$lang = $l;
						break;
					}
				}
			}else{
				throw new NotFound;
			}
		}
		return $lang;
	}
	static function routing(){
		$found = false;
		$api = loader::sapi();
		if($api == loader::cgi){
			$path = http::$request['uri'];
			$uri = explode('/', $path);
			array_splice($uri, 0, 1);
			$changelang = options::get('packages.base.translator.changelang');
			if($changelang == 'uri'){
				if($uri[0]){
					$lang = self::CheckShortLang($uri[0]);
					try{
						translator::setLang($lang);
						array_splice($uri, 0, 1);
					}catch(InvalidLangCode $e){
						throw new NotFound;
					}
				}
			}elseif($changelang == 'parameter'){
				if($lang = http::getURIData('lang')){
					$lang = self::CheckShortLang($lang);
					try{
						translator::setLang($lang);
					}catch(InvalidLangCode $e){
						throw new NotFound;
					}
				}
			}

			$newuri = array();
			foreach($uri as $p){
				if($p){
					$newuri[] = $p;
				}
			}
			$uri = $newuri;
			if(empty($uri)){
				$uri = array('index');
			}
			foreach(self::$rules as $rule){
				if(($data = self::checkRule($rule, $uri)) !== false){
					list($controller, $method) = explode('@', $rule['controller'], 2);
					if(preg_match('/^\\\\packages\\\\([a-zA-Z0-9|_]+).*$/', $controller, $matches)){
						if($package = packages::package($matches[1])){
							//$package->register_autoload();
							$package->bootup();
							if(class_exists($controller) and method_exists($controller, $method)){
								//$package->applyFrontend();
								$controllerClass = new $controller();
								$controllerClass->response($controllerClass->$method($data));
								//$package->cancelFrontend();
							}else{
								throw new routerController($rule['controller']);
							}
						}
					}
					$found = true;
					break;
				}
			}
		}else{
			if(($processID = cli::getParameter('process')) !== false){
				$process = process::byId($processID);
				if($process->status != process::running){
					list($controller, $method) = explode('@', $process->name, 2);
					if(class_exists($controller) and method_exists($controller, $method)){
						$process = new $controller($process);
						$process->setPID();
						$return = $process->$method($process->parameters);
						if($return instanceof response){
							$process->status = $return->getStatus() ? process::stopped : process::error;
							$process->response = $return;
						}
						$process->save();
					}else{
						throw new proccessClass($process->name);
					}
				}else{
					throw new proccessAlive($process->id);
				}
			}

		}
		return $found;
	}
	private static function checkRule($rule, $uri){
		if($rule['method'] == '' or $rule['method'] == strtolower(http::$request['method'])){
			if(count($rule['path']) == count($uri)){
				$wrong = false;
				$data = array();
				$len = count($rule['path']);
				for($x=0;$x!=$len;$x++){
					$part = $rule['path'][$x];
					if($part['type'] == 'static'){
						if($part['name'] != $uri[$x]){
							$wrong = true;
						}
					}elseif($part['type'] == 'dynamic'){
						if(isset($part['regex'])){
							if(!preg_match($part['regex'], $uri[$x])){
								$wrong = true;
							}
						}
						if(!$wrong){
							$data[$part['name']] = $uri[$x];
						}
					}
					if($wrong){
						break;
					}
				}
				if(!$wrong){
					return $data;
				}
			}
		}
		return false;
	}
}
?>
