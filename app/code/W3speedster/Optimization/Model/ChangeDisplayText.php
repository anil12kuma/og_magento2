<?php
namespace W3speedster\Optimization\Model;

use W3speedster\Optimization\Helper\Data;
use \Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManage;
use Magento\Framework\App\CacheInterface;

class ChangeDisplayText implements \Magento\Framework\Event\ObserverInterface
{
    protected $_settings;
	protected $_helper;
    protected $dir;
	protected $request = [];
    public function __construct(
        Data $helper,Context $context, \Magento\Framework\Filesystem\DirectoryList $dir
    )
    {		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->objectManager = $objectManager;
		$this->request = $objectManager->get('Magento\Framework\App\Request\Http');
		$directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$this->dir = $directory;
        $this->_helper = $helper;
    }
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_settings = $this->_helper->getW3Settings();		
        $hooks = $this->_helper->getHooksBeforeOpt();        
        $this->_settings['documentRoot']  =  $this->dir->getPath('pub');		
        $this->_settings['rootCachePath'] = (!empty($this->_settings['cache_path']) ? $this->_settings['cache_path'] : $this->_settings['documentRoot'].'/media/cache');
        if(!empty($this->request->getParam('types')) && (in_array("critical",explode(",",implode($this->request->getParam('types')))))){					
            $this->delete_directory($this->_settings['rootCachePath']);
        } elseif(!empty($this->request->getParam('types')) && (in_array("optimization",explode(",",implode($this->request->getParam('types')))))) {
			$this->updateDataFile('w3_rand_key',rand(10,10000),0);
            $this->delete_directory(rtrim($this->_settings['rootCachePath'],'/').'/all');
            $this->delete_directory(rtrim($this->_settings['rootCachePath'],'/').'/all-css');
            $this->delete_directory(rtrim($this->_settings['rootCachePath'],'/').'/all-js');
            $this->delete_directory(rtrim($this->_settings['rootCachePath'],'/').'/js');
            $this->delete_directory(rtrim($this->_settings['rootCachePath'],'/').'/css');
        }	
        
    }
	public function updateDataFile($filename,$html,$array = 1){
        $path = $this->_settings['rootCachePath'].'/'.$filename.'.php';
        $file = fopen($path,'w');

        fwrite($file,($array ? json_encode($html) : $html));

        fclose($file);
    }
    public function delete_directory($dirname) {
        
        if (is_dir($dirname))
            $dir_handle = opendir($dirname);
         if (!isset($dir_handle))
              return false;
        while($file = readdir($dir_handle)) {
               if ($file != "." && $file != "..") {
                    if (!is_dir($dirname."/".$file))
                         @unlink($dirname."/".$file);
                    else
                        $this->delete_directory($dirname.'/'.$file);
               }
        }
        closedir($dir_handle);
        @rmdir($dirname);
        return true;
    }
    
    
}