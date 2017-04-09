<?php
namespace packages\base\IO\directory;
use \packages\base\IO\file;
use \packages\base\IO\directory;
use \packages\base\IO\NotFoundException;
class local extends directory{
    public function size(): int{
        return 0;
    }
    public function move(directory $dest): bool{
        
    }
    public function rename(string $newName): bool{
        
    }
    public function delete(){
		foreach($this->items(false) as $item){
			$item->delete();
		}
		rmdir($this->getPath());
    }
    public function make(bool $recursive = false):bool{
        if($recursive){
            $dirs = explode('/', $this->getPath());
            $dir='';
            foreach ($dirs as $part) {
                $dir .= $part.'/';
                if ($dir and !is_dir($dir)){
                    if(!mkdir($dir)){
                        return false;
                    }
                }
            }
            return true;
        }else{
            return mkdir($this->getPath());
        }
    }
    public function copyTo(directory $dest): bool{
        $sourcePath = $this->getPath();
		if(!$dest->exists()){
			$dest->make(true);
		}
        foreach($this->items(true) as $item){
            $relativePath = substr($item->getPath(), strlen($sourcePath)+1);
            if($item instanceof file){
                if(!$item->copyTo($dest->file($relativePath))){
					return false;	
				}
            }else{
				$destDir = $dest->directory($relativePath);
				if(!$destDir->exists()){
					if(!$destDir->make(true)){
						return false;	
					}
				}
			}
        }
		return true;
    }
    public function copyFrom(directory $source): bool{
        
    }
    public function files(bool $recursively = true):array{
        $scanner = function($dir) use($recursively, &$scanner){
            $files = [];
            foreach(scandir($dir) as $item){
                if($item != '.' and $item != '..'){
                    if(is_file($dir.'/'.$item)){
                        $files[] = new file\local($dir.'/'.$item);
                    }elseif($recursively){
                        $files = array_merge($files, $scanner($dir.'/'.$item));
                    }
                }
            }
            return $files;
        };
        return $scanner($this->getPath());
    }
    public function directories(bool $recursively = true):array{
        $scanner = function($dir) use($recursively, &$scanner){
            $items = [];
            foreach(scandir($dir) as $item){
                if($item != '.' and $item != '..'){
                    if(is_dir($dir.'/'.$item)){
                        $items[] = new directory\local($dir.'/'.$item);
                        if($recursively){
                            $items = array_merge($items, $scanner($dir.'/'.$item));
                        }
                    }
                }
            }
            return $items;
        };
        return $scanner($this->getPath());
    }
    public function items(bool $recursively = true):array{
        $scanner = function($dir) use($recursively, &$scanner){
            $items = [];
            foreach(scandir($dir) as $item){
                if($item != '.' and $item != '..'){
                    if(is_file($dir.'/'.$item)){
                        $items[] = new file\local($dir.'/'.$item);
                    }else{
                        $items[] = new directory\local($dir.'/'.$item);
                        if($recursively){
                            $items = array_merge($items, $scanner($dir.'/'.$item));
                        }
                    }
                }
            }
            return $items;
        };
        return $scanner($this->getPath());
    }
    public function exists():bool{
        return is_dir($this->getPath());
    }
    public function file(string $name):file\local{
        return new file\local($this->getPath().'/'.$name);
    }
    public function directory(string $name):directory\local{
        return new directory\local($this->getPath().'/'.$name);
    }
	public function getRealPath():string{
		return realpath($this->getPath());
	}
}