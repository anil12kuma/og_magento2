<?php
namespace W3speedster\Optimization\Model\admin;
use W3speedster\Optimization\Model\includes\W3speedster;

class w3speedsterAdmin extends W3speedster{
    var $w3_images;
    function launch($settings){
        $this->settings = $settings;		
        if(!empty($this->request->getParam('action')) && $this->request->getParam('action') == 'W3speedsterValidateLicenseKey'){
            $this->W3speedsterValidateLicenseKey();
        }
        if($this->request->getParam('license_key')){
            $this->w3_save_options();
        }
        if(!empty($this->request->getParam('w3_reset_preload_css'))){
            $this->updateDataFile('w3speedsterPreloadCss','');
            add_action( 'admin_notices', array($this,'w3_admin_notice_import_success') );
        }
        if(!empty($this->request->getParam('restart'))){
            $this->updateDataFile('w3speedster_img_opt_status',0);
        }
        if(!empty($this->request->getParam('reset'))){
            $this->updateDataFile('w3speedster_opt_offset',0);
        }
    }
    
    function w3CalcImages($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object){
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) != "dir"){
                        if(pathinfo($dir."/".$object, PATHINFO_EXTENSION) == "jpeg" || pathinfo($dir."/".$object, PATHINFO_EXTENSION) == "jpg" || pathinfo($dir."/".$object, PATHINFO_EXTENSION) == "png"){
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
    function w3_save_options(){
        if(isset($_POST['ws_action']) && $_POST['ws_action'] == 'cache'){
            unset($_POST['ws_action']);
            foreach($_POST as $key=>$value){
                $array[$key] = $value;
            }
            if(empty($array['license_key'])){
                $array['is_activated'] = '';
            }
            $this->updateDataFile( 'w3_speedup_option', $array );		
            $this->settings = $this->getDataFile( 'w3_speedup_option');			
        }
    }
    function getCurlUrl($url, $curl){
      return parent::getCurlUrl($url, $curl);
    } 

}