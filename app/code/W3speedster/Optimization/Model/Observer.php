<?php
namespace W3speedster\Optimization\Model;


use W3speedster\Optimization\Model\includes\w3speedsterHtmlOptimize;

use W3speedster\Optimization\Model\includes\w3speedsterOptimizeImage;

use W3speedster\Optimization\Model\includes\W3speedster;

use \Magento\Framework\Event\ObserverInterface;

use W3speedster\Optimization\Helper\Data;

use \Magento\Framework\App\Helper\Context;

use	\Magento\Framework\HTTP\Client\Curl;

use \Magento\Framework\App\Request\Http;


if(!defined('W3SPEEDSTER_PATH'))
    define('W3SPEEDSTER_PATH',__dir__);

class Observer implements ObserverInterface
{
    protected $_helper;
    protected $_curl;
    public $w3_images;
    protected $dir;
	protected $request;
	
    public function __construct(
		Data $helper,
		Context $context,
		Curl $curl,
		\Magento\Framework\Filesystem\DirectoryList $dir,
		Http $request
	) {		
        $this->dir = $dir;
		$this->request = $request;
        $this->_helper = $helper;
        $this->_curl = $curl;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {      
        $settings = $this->_helper->getW3Settings();
        $hooks = $this->_helper->getHooksBeforeOpt();		
        $response = $observer->getEvent()->getData('response');		
        if(!$response)
            return;
            
        $html = $response->getBody();
        if($html == ''){
			return;
		}			
        include_once(W3SPEEDSTER_PATH .'/includes/W3speedster.php');
        $settings = $this->_helper->getW3Settings();		
        $hooks = $this->_helper->getHooksBeforeOpt();
        $w3_speedup = new W3speedster($settings,$hooks,$this->dir,$this->request);
            
        if(!empty($this->request->getParam('action')) && $this->request->getParam('action') == 'W3speedsterValidateLicenseKey' ){
            $w3_speedup->w3speedster_activate_license_key();
            exit;
        }
        if(!empty($this->request->getParam('action')) && $this->request->getParam('action') == 'countimages'){
            
            $img_arr = $w3_speedup->getDataFile("w3_images",1);
            
            if(!empty($this->request->getParam('reset_img')) || count($img_arr) == 0){			
                if(!empty($w3_speedup->add_settings['uploadPath'])){
                    $this->w3CalcImages($w3_speedup->add_settings['uploadPath']);
                    $w3_speedup->updateDataFile("w3_images",$this->w3_images,1);						
                    $img_to_opt = count($this->w3_images);
                    $img_remaining = count($this->w3_images);
                    $w3_speedup->updateDataFile("w3_images_count",$img_to_opt,0);
                }
            }else{
                $img_remaining = count($img_arr);
                $img_to_opt = $w3_speedup->getDataFile("w3_images_count",0);
            }
            
            $img_to_opt = empty($img_to_opt) ? 1 : $img_to_opt;
            $opt_offset = (int)$w3_speedup->getDataFile('w3speedster_opt_offset');			
            $data = array('img_to_opt' => $img_to_opt,'opt_offset'=>$opt_offset,'img_remaining'=>$img_remaining);					
            echo json_encode($data);
            exit;
        }
        if(!empty($this->request->getParam('action')) && $this->request->getParam('action') == 'w3speedsterOptimizeImage'){
            $this->w3speedsterImageOptimizationCallback($this->_curl);
        }
        if(!empty($this->request->getParam('action')) && $this->request->getParam('action') == 'w3speedsterPreloadCss'){
            $w3_speedup->updateDataFile('w3speedsterCriticalCssError','',1);
			$this->w3speedsterPreloadCssCallback();
			$totalcss = $w3_speedup->getDataFile('w3speedsterPreloadCss',1);				
			$error = $w3_speedup->getDataFile('w3speedsterCriticalCssError',1);				
			$total = (int)$w3_speedup->getDataFile('w3speedsterPreloadCssTotal',0);
			$created = (int)$w3_speedup->getDataFile('w3speedsterPreloadCss_created',0);
			if(count($totalcss) == 0 ){
				$error[0] = 'ALL Css done! No Url Crolled.';
			}
			if(!empty($error[0]) && $error[0] != "" && $error[0] != NULL){
				echo json_encode(array('error',$error,$total,$created));
			}else{
				echo json_encode(array('success',1,$total,$created));
			}
			exit;
        }
        if(!empty($this->request->getParam('w3PreloadCss'))){
			$this->w3speedsterPreloadCssCallback();
			exit;
		}
        if(!empty($this->request->getParam('w3PutPreloadCss'))){
            $this->w3speedsterPutPreloadCssCallback();
            exit;
        }
        
        
        require_once('includes/w3speedsterCss.php');
        require_once('includes/w3speedsterJs.php');
        require_once('includes/w3speedsterHtmlOptimize.php');
        if ($this->request->getFullActionName() == 'cms_noroute_index') {
           
        } else {
            $w3_optimize = new w3speedsterHtmlOptimize($settings,$hooks,$this->dir,$this->request);
            $html = $w3_optimize->start($html,$this->_curl);;
        }
        $response->setBody($html);
         
        
    }
    function w3CalcImages($dir) {	
        $settings = $this->_helper->getW3Settings();
        $hooks = $this->_helper->getHooksBeforeOpt();
        $w3_speedup = new W3speedster($settings,$hooks,$this->dir,$this->request);
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object){
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) != "dir"){
                        if(pathinfo($dir."/".$object, PATHINFO_EXTENSION) == "jpeg" || pathinfo($dir."/".$object, PATHINFO_EXTENSION) == "jpg" || pathinfo($dir."/".$object, PATHINFO_EXTENSION) == "png"){
                            $imgarray = $w3_speedup->getDataFile("w3_images",1);
                            $this->w3_images[] = $dir."/".$object;
                        }
                    }else{
                        $this->w3CalcImages($dir."/".$object);
                    }
                }
            }
            reset($objects);
        }
    }
    function w3speedsterOptimizeImage_on_upload($metadata, $attachment_id, $context="create"){
        require_once(W3SPEEDSTER_PATH . '/includes/w3speedsterOptimizeImage.php');
        $w3_speedup_opt_img = new w3speedsterOptimizeImage();
        return $w3_speedup_opt_img->w3speedsterOptimizeSingleImage($metadata, $attachment_id, $context);
    }
    
    function w3speedsterImageOptimizationCallback($curl){
        include_once(W3SPEEDSTER_PATH . '/includes/w3speedsterOptimizeImage.php');
        $settings = $this->_helper->getW3Settings();
        $hooks = $this->_helper->getHooksBeforeOpt();
        $w3_speedup_opt_img = new w3speedsterOptimizeImage($settings,$hooks,$this->dir,$this->request);
        $w3_speedup_opt_img->w3speedsterOptimizeImageCallback($curl);
    }
    function w3speedster_activate_license_key(){
        include_once(W3SPEEDSTER_PATH .'/admin/w3speedsterAdmin.php');
        $w3_speedup = new w3speedster(); 
        $w3_speedup->W3speedsterValidateLicenseKey($this->_curl);
        exit;
    }
    function w3speedsterPutPreloadCssCallback(){
        include_once(W3SPEEDSTER_PATH .'/admin/w3speedsterAdmin.php');
        $w3_speedup = new w3speedster(); 
        $w3_speedup->w3PutPreloadCss();
        exit;
    }
    function w3speedsterPreloadCssCallback(){
        $settings = $this->_helper->getW3Settings();
        $hooks = $this->_helper->getHooksBeforeOpt();
        include_once(W3SPEEDSTER_PATH .'/admin/w3speedsterAdmin.php');
        include_once(W3SPEEDSTER_PATH .'/includes/W3speedster.php');		
        $w3_speedup = new W3speedster($settings,$hooks,$this->dir,$this->request); 
        $w3_speedup->w3GeneratePreloadCss($this->_curl);
        if(!empty($this->request->getParam('w3PreloadCss'))){
            exit;
        }
    }
    function w3speedster_add_image_optimization_schedule(){		
        $this->w3speedsterImageOptimizationCallback($this->_curl);
        exit;
    }	
    

}
