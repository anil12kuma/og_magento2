<?php
namespace W3speedster\Optimization\Model\includes;

class W3speedster{	
    public $addSettings = [];
    public $settings = [];
	public $hooks = [];
	public $objectManager = [];
	public $request = [];
	public $httpHeader = [];
    public function __construct($settings,$hooks,$dir,$req){
        $this->settings = $settings;
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->objectManager = $objectManager;
		$this->httpHeader = $this->objectManager->get('\Magento\Framework\HTTP\Header');
		$storeManager = $this->objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$directory = $this->objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$urlInterface = $this->objectManager->get(\Magento\Framework\UrlInterface::class);
 		$this->request = $objectManager->get('Magento\Framework\App\Request\Http');
		$this->settings = !empty($this->settings) && is_array($this->settings) ? $this->settings : array();
        $this->addSettings = array();
        $mediaPath = $dir->getPath('media');        
        $documentRoot  =  $dir->getPath('pub');
		$mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		
	    if(strpos($mediaUrl,'/pub/') !== false){
            $this->addSettings['documentRoot'] = str_replace('/pub/media','',$mediaPath);
        }else{
			$this->addSettings['documentRoot']  =  $dir->getPath('pub');
		}
		
		$this->addSettings['homeUrlCss'] = rtrim($storeManager->getStore()->getBaseUrl(),'/');
        $this->addSettings['homeUrl'] = rtrim($storeManager->getStore()->getBaseUrl(),'/');/* edit */
        $this->addSettings['secure'] = strpos($this->addSettings['homeUrl'],'https:') !== false ? 'https://' : 'http://'; 
        
        $this->addSettings['imageHomeUrl'] = !empty($this->settings['cdn']) ? $this->settings['cdn'] : $this->addSettings['homeUrl'];
        $this->addSettings['w3ApiUrl'] = !empty($this->settings['w3ApiUrl']) ? $this->settings['w3ApiUrl'] : 'https://cloud.W3speedster.com/optimize/';		
        $this->addSettings['W3speedster_url'] = $this->addSettings['homeUrl'].'/images';
        $this->addSettings['fullUrl'] = $urlInterface->getCurrentUrl();		
        $full_url_array = explode('?',$this->addSettings['fullUrl']);
        $this->addSettings['fullUrlWithoutParam'] = $full_url_array[0];
        $this->addSettings['rootCachePath'] = (!empty($this->settings['cache_path']) ? $this->settings['cache_path'] : $mediaPath.'/cache');
        $this->addSettings['criticalCssPath'] = $this->addSettings['rootCachePath'].'/critical-css';
        $this->addSettings['cacheUrl'] = str_replace($this->addSettings['documentRoot'],$this->addSettings['homeUrl'],$this->addSettings['rootCachePath']);
        $this->addSettings['uploadPath'] = $mediaPath;
        $this->addSettings['optimize_image_path'] = $mediaPath;
        $this->addSettings['webp_upload_path'] = $mediaPath.'/w3-webp';
		$this->addSettings['webp_path'] = '/w3-webp/media';
        $this->addSettings['upload_url'] = $this->addSettings['homeUrl'];
        $this->addSettings['webp_url'] = $this->addSettings['upload_url'].'/w3-webp/';
        $this->addSettings['upload_base_url'] = $mediaUrl;
        $this->addSettings['upload_base_dir'] = $mediaPath;
        $useragent= $this->httpHeader->getHttpUserAgent();
        $this->addSettings['is_mobile'] = 0;
		if(!empty($useragent)){
			if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
				$this->addSettings['is_mobile'] = 1 ;
			}
		}
        $this->addSettings['load_ext_js_before_internal_js'] = !empty($this->settings['load_external_before_internal']) ? explode("\r\n", $this->settings['load_external_before_internal']) : array();
        $this->addSettings['load_js_for_mobile_only'] = !empty($this->settings['load_js_for_mobile_only']) ? $this->settings['load_js_for_mobile_only'] : '';
        $this->addSettings['w3_rand_key'] = $this->getDataFile('w3_rand_key', 0);
        if($this->addSettings['is_mobile'] && $this->addSettings['load_js_for_mobile_only'] == 1){
            $this->settings['load_combined_js'] = 'after_page_load';
        }
        if(!empty($this->settings['css_mobile']) && $this->addSettings['is_mobile']){
            $this->addSettings['css_ext'] = 'mob.css';
            $this->addSettings['js_ext'] = 'mob.js';
            $this->addSettings['preload_css'] = !empty($this->settings['preload_css_mobile']) ? explode("\r\n", $this->settings['preload_css_mobile']) : array();
        }else{
            $this->addSettings['css_ext'] = '.css';
            $this->addSettings['js_ext'] = '.js';
            $this->addSettings['preload_css']  = !empty($this->settings['preload_css']) ? explode("\r\n", $this->settings['preload_css']) : array();
        }
        $this->addSettings['preload_css_url'] = array();
        $this->addSettings['headers'] = function_exists('getallheaders') ? getallheaders() : array();
        $this->addSettings['main_css_url'] = array();
        $this->addSettings['lazy_load_js'] = array();
        $this->addSettings['exclude_cdn'] = !empty($this->settings['exclude_cdn']) ? explode(',',str_replace(' ','',$this->settings['exclude_cdn'])) : '';
        $this->addSettings['webp_enable'] = array();
        $this->addSettings['webp_enable_instance'] = array($this->addSettings['upload_url']);
        $this->addSettings['webp_enable_instance_replace'] = array($this->addSettings['webp_url']);
        $this->settings['webp_png'] = isset($this->settings['webp_png']) ? $this->settings['webp_png'] : '';
        $this->settings['webp_jpg'] = !empty($this->settings['webp_jpg']) ? $this->settings['webp_jpg'] : '';
        if($this->settings['webp_jpg'] == 1){
            $this->addSettings['webp_enable'] = array_merge($this->addSettings['webp_enable'],array('.jpg','.jpeg'));
            $this->addSettings['webp_enable_instance'] = array_merge($this->addSettings['webp_enable_instance'],array('.jpg?','.jpeg?','.jpg','.jpeg','.jpg"','.jpeg"',".jpg'",".jpeg'"));
            $this->addSettings['webp_enable_instance_replace'] = array_merge($this->addSettings['webp_enable_instance_replace'],array('.jpgw3.webp?','.jpegw3.webp?','.jpgw3.webp','.jpegw3.webp','.jpgw3.webp"','.jpegw3.webp"',".jpgw3.webp'",".jpegw3.webp'"));
        }
        if($this->settings['webp_png'] == 1){
            $this->addSettings['webp_enable'] = array_merge($this->addSettings['webp_enable'],array('.png'));
            $this->addSettings['webp_enable_instance'] = array_merge($this->addSettings['webp_enable_instance'],array('.png?','.png','.png"',".png'"));
            $this->addSettings['webp_enable_instance_replace'] = array_merge($this->addSettings['webp_enable_instance_replace'],array('.pngw3.webp?','.pngw3.webp','.pngw3.webp"',".pngw3.webp'"));
        }
		$this->addSettings['w3_extension'] = 'w3.webp';
        $this->addSettings['htaccess'] = 0;
        if(is_file($this->addSettings['documentRoot']."/.htaccess")){
            $htaccess = file_get_contents($this->addSettings['documentRoot']."/.htaccess");
            if(strpos($htaccess,'W3WEBP') !== false){
                $this->addSettings['htaccess'] = 1;
            }
        }
        $this->addSettings['critical_css'] = '';
        $this->addSettings['starttime'] = microtime();
        
        if(!empty($this->settings['imageHomeUrl'])){
            $this->settings['imageHomeUrl'] = rtrim(rtrim($this->settings['imageHomeUrl']),'/');
        }
        $this->w3RecurseCopy(W3SPEEDSTER_PATH .'/assets',$mediaPath.'/cache');		
    }

    function w3RecurseCopy($src,$dst) {	
		if(!is_dir($dst)){
            mkdir($dst);
        }
		$image_blank_file = array('blank-h.png','blank-square.png','blank-p.png','blank.png','blank.mp4','blank.mp3','blank.pngw3.webp','blank-4x3.png','blank-3x4.png');
		foreach($image_blank_file as $file){
			if(!is_file($dst.'/'.$file)){
				copy(W3SPEEDSTER_PATH."/assets/images".'/'.$file,$dst.'/'.$file);
			}
		}
        
        if(!is_file($dst.'/blank.css')){
            $file = fopen($dst.'/blank.css','w');
            fwrite($file,'/*blank.css*/');
            fclose($file);
        }
    } 
    function updateDataFile($filename,$html,$array = 1){
        $path = $this->addSettings['rootCachePath'].'/'.$filename.'.php';
        $file = fopen($path,'w');

        fwrite($file,($array ? json_encode($html) : $html));

        fclose($file);
    }
    function getDataFile($filename,$is_array =1){
        if(is_file($this->addSettings['rootCachePath'].'/'.$filename.'.php')){
            if($is_array){
                return (array)json_decode(file_get_contents($this->addSettings['rootCachePath'].'/'.$filename.'.php'));
            }else{
                return file_get_contents($this->addSettings['rootCachePath'].'/'.$filename.'.php');
            }
        }
        if($is_array){
            return array();
        }else{
            return '';
        }
    }

    function w3DebugTime($html,$process){
        if(!empty($this->request->getParam('w3Debug'))){
            $starttime = !empty($this->addSettings['starttime']) ? $this->addSettings['starttime'] : microtime(true);
            $endtime = microtime(true);
            return $html.$process.'-'.$this->formatPeriod($endtime ,$starttime);
        }
        return $html;
    }
    function formatPeriod($endtime, $starttime){

        $duration = $endtime - $starttime;

        $hours = (int) ($duration / 60 / 60);

        $minutes = (int) ($duration / 60) - $hours * 60;

        $seconds = (int) $duration - $hours * 60 * 60 - $minutes * 60;

        return ($hours == 0 ? "00":$hours) . ":" . ($minutes == 0 ? "00":($minutes < 10? "0".$minutes:$minutes)) . ":" . ($seconds == 0 ? "00":($seconds < 10? "0".$seconds:$seconds));
    }
    function w3StrReplaceLast( $search , $replace , $str ) {
        if( ( $pos = strrpos( $str , $search ) ) !== false ) {
            $search_length  = strlen( $search );
            $str    = substr_replace( $str , $replace , $pos , $search_length );
        }
        return $str;
    }
    function W3speedsterValidateLicenseKey($curl){
        if(!empty($this->request->getParam('key'))){
			$key = $this->request->getParam('key');
            $response = $this->getCurlUrl($this->addSettings['w3ApiUrl'].'get_license_detail.php?'.'license_id='.$key.'&domain='.base64_encode($this->addSettings['homeUrl']), $curl);
            if(!empty($response)){
                $res_arr = json_decode($response);				
                if($res_arr[0] == 'success'){
                    echo json_encode(array('success','verified',$res_arr[1])); exit;
                }else{
                    echo json_encode(array('fail','could not verify-1'.$response)); exit;
                }
            }else{
                echo json_encode(array('fail','could not verify-2')); exit;
            }
        }else{
            echo json_encode(array('fail','could not verify-3')); exit;
        }
        exit;
    }
    function w3ParseUrl($src){
        $pattern = '/(.*)\/\/'.str_replace('/','\/',str_replace($this->addSettings['secure'],'',rtrim($this->addSettings['homeUrl'],'/'))).'(.*)/';
        $src = preg_replace($pattern, '$2', $src);
        $src_arr = parse_url($src);
        return $src_arr;
    }
    
    function w3IsExternal($url) {
       $components = parse_url($url);
        return !empty($components['host']) && strcasecmp($components['host'], $_SERVER['HTTP_HOST']);
    }

    function w3Endswith($string, $test) {
        $str_arr = explode('?',$string);
        $string = $str_arr[0];
        $ext = '.'.pathinfo($str_arr[0], PATHINFO_EXTENSION);
        if($ext == $test)
            return true;
        else
            return false;
    }
    function w3GeneratePreloadCss($curl){
		if(empty($this->settings['optimization_on'])){
			return;
		}
		if(!empty($this->request->getParam('url'))){
			$key_url = $this->request->getParam('url');
		}
		$preload_css_new = $preload_css = $this->getDataFile('w3speedsterPreloadCss');
		if(!empty($preload_css)){
			foreach($preload_css as $key1 => $url){
				if(!empty($key_url) && strpos($key1,$key_url) !== false){
					unset($preload_css_new[$key1]); 
					continue;
				}
				$key = base64_decode($key1);
				if(!empty($key_url) && !empty($preload_css[base64_encode($key_url)])){
					$key = $key_url; 
					$url = $preload_css[base64_encode($key_url)];
					$key_url = '';
				}
				$this->w3Echo('rocket1'.$key.$url[0].$url[1]);
				if(empty($url[2])){
					$this->w3Echo('rocket2-deleted');
					unset($preload_css_new[$key1]);
					continue;
				}
				$response = $this->w3CreatePreloadCss($key, $url[0], $url[2], $curl);
				
				if(!empty($response) && $response === "exists"){
					unset($preload_css_new[$key1]);
					continue;
				}
				if(!empty($response) && $response === "hold"){
					$this->w3Echo('rocket5'.$response);
					break;
				}
				if($response || $preload_css[$key1][1] == 1){
					$this->w3Echo('rocket4'.$response);
					unset($preload_css_new[$key1]);
				}else{
					$this->w3Echo('rocket6');
					$preload_css_new[$key1][1] = 1;
				}
				break;
			}
			$this->updateDataFile('w3speedsterPreloadCss',$preload_css_new,1);
		}
	}
    
	function w3CreatePreloadCss($url,$filename, $css_path,$curl){
        if(!empty($this->request->getParam('key')) && $this->settings['license_key'] == $this->request->getParam('key')){
            $this->w3RemoveCriticalCssCacheFiles($curl);
        }
        $this->w3Echo('rocket2'.$filename.$url);
        $this->w3Echo('rocket3'.$css_path);
        if(is_file($css_path.'/'.$filename)){
            $this->w3Echo('rocket9');
            return true;
        }
        $nonce = rand(10,100000);
        $this->updateDataFile("purge_critical_css",$nonce,0);
        if($this->addSettings['homeUrl'] != $this->addSettings['imageHomeUrl']){
            $css_urls = $this->addSettings['homeUrlCss'].','.$this->addSettings['imageHomeUrl'];
        }else{
            $css_urls = $this->addSettings['homeUrlCss'];
        }		
        $options = 'url='.$url.'&'.'key='.$this->settings['license_key'].'&_wpnonce='.$nonce.'&filename='.$filename.'&css_url='.$css_urls.'&path='.$css_path.'&html=';
            $options1 = $options;
            
        $response = $this->getCurlUrl($this->addSettings['w3ApiUrl'].'css/index-magento.php?'.$options, $curl);
        
        $this->w3Echo('<pre>'); $this->w3PrintR($options1);
        
        if( !empty( $response ) ) {
            $this->w3Echo('rocket3'.$css_path.'/'.$filename);
            $this->w3Echo($response);
            if(!empty($response)){
                $response_arr = (array)json_decode($response);
                
                if(!empty($response_arr['result']) && $response_arr['result'] == 'success'){
					$this->w3_check_if_folder_exists($css_path);
                    $this->w3CreateFile($css_path.'/'.$filename, $response_arr['w3_css']);
                    $preload_css = $this->getDataFile('w3speedsterPreloadCss',1);
                    unset($preload_css[$response_arr['url']]);
                    $this->updateDataFile('w3speedsterPreloadCss',$preload_css,1);
                    $this->updateDataFile('w3speedsterPreloadCss_created',(int)$this->getDataFile('w3speedsterPreloadCss_created',0)+1,0);
                    if(is_file($file = $this->w3GetFullUrlCachePath($url).'/main_css.json')){
                        unlink($file);
                    }
                    return true;
                }elseif(!empty($response_arr['error'])){
                    if($response_arr['error'] == 'process already running'){
                        return 'hold';
                    }else{
                        $this->w3Echo('rocket-error'.$response_arr['error']);
                        $this->updateDataFile('w3speedsterCriticalCssError',$response_arr['error'],1);
                        return false;
                    }
                }
                $this->w3Echo('rocket7');
                return false;
            }else{
                $this->w3Echo('rocket8');
                return false;
            }
        }else{
            return false;
        }
        
    }
    function w3PrintR($text){
        if(!empty($this->request->getParam('w3PreloadCss'))){
            print_r($text);
        }
    }
    function w3Echo($text){
        if(!empty($this->request->getParam('w3PreloadCss'))){
            echo $text;
        }
    }
    function w3PreloadCssPath($url=''){        
        $url = empty($url) ? $this->addSettings['fullUrlWithoutParam'] : $url;
        if(!empty($this->addSettings['preload_css_url'][$url])){
            return $this->addSettings['preload_css_url'][$url];
        }
		$page_request = $this->objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
        if(rtrim($url,'/') == rtrim($this->addSettings['homeUrl'],'/')){
            
        }else{
			if ($page_request->getFullActionName() == 'catalog_product_view') {
                $url = rtrim($this->addSettings['homeUrl'],'/').'/'.'catalog_product_view';
            }
            if ($page_request->getFullActionName() == 'catalog_category_view') {
                $url = rtrim($this->addSettings['homeUrl'],'/').'/'.'catalog_category_view';
            }
            if (strpos($url,'customer/account') !== false) {
                $url = rtrim($this->addSettings['homeUrl'],'/').'/customer';
            }
        }
        $full_url = str_replace($this->addSettings['secure'],'',rtrim($url,'/'));
        $path = $this->w3GetCriticalCachePath($full_url);
        $this->addSettings['preload_css_url'][$url] = $path;
        return $path;
    }
    function w3PutPreloadCss(){
        if (  null == $this->request->getParam('_wpnonce') || $this->request->getParam('_wpnonce') != $this->getDataFile('purge_critical_css')) {
            echo 'Request not valid'; exit;
        }
        if(!empty($this->request->getParam('url')) && !empty($this->request->getParam('filename')) && !empty($this->request->getParam('w3_css'))){
            $filename = $this->request->getParam('filename');
			$url = $this->request->getParam('url');
			$w3Css = $this->request->getParam('w3_css');
            $preload_css = $this->getDataFile('w3speedsterPreloadCss');
			$path = $this->request->getParam('path');
            echo $path = !empty($preload_css[$filename][2]) ? $preload_css[$filename][2] : $path;
            $this->w3CreateFile($path.'/'.$filename, stripslashes($w3Css));
            
            /*if(is_file($file = $this->w3GetFullUrlCachePath($url).'/main_css.json')){
                unlink($file);
            }*/
            echo 'saved';
        }
        echo false;
        exit;
    }
    function w3CreateFile($path, $text = '//'){    
        $file = fopen($path,'w');
        fwrite($file,$text);
        fclose($file);
        /*if(is_writable($path)){
			chmod($path, 0644); 
		}*/
        return true;
    }

    function w3ParseLink($tag,$link){
        $xmlDoc = new \DOMDocument();
        if (@$xmlDoc->loadHTML($link) === false){
            return array();
        }
        $tag_html = $xmlDoc->getElementsByTagName($tag);
        $link_arr = array();
        if(!empty($tag_html[0])){
            foreach ($tag_html[0]->attributes as $attr) {
                $link_arr[$attr->nodeName] = $attr->nodeValue;
            }
        }
        return $link_arr;
    }

    function w3ImplodeLinkArray($tag,$array){
        $link = '<'.$tag.' ';
        foreach($array as $key => $arr){
            $link .= $key.'="'.$arr.'" ';
        }
        if($tag == 'script'){
            $link .= '></script>';
        }else{
            $link .= '/>';
        }
        return $link;
    }
    function w3InsertContentHeadInJson(){
        global $insert_content_head;
        $file = $this->w3GetFullUrlCachePath().'/content_head.json';
        //$this->w3CreateFile($file,json_encode($insert_content_head));
    }
    
    function w3InsertContentHead($html, $content, $pos){
        global $insert_content_head;
        $insert_content_head[] = array($content,$pos);
        if($pos == 1){    
            $html = preg_replace('/<style/',  $content.'<style', $html, 1);    
        }elseif($pos == 2){    
            $html = preg_replace('/<link(.*)href="([^"]*)"(.*)>/',$content.'<link$1href="$2"$3>',$html,1);    
        }else{    
            $html = preg_replace('/<script/',  $content.'<script', $html, 1);    
        }    
        return $html;    
    }
    function w3_main_css_url_to_json(){
        global $main_css_url;
        if(empty($main_css_url)){
            $main_css_url = array();
        }
        $file = $this->w3GetFullUrlCachePath().'/main_css.json';
        //$this->w3CreateFile($file,json_encode($main_css_url));
    }
    function w3_internal_js_to_json(){
        global $internal_js;
        if(empty($internal_js)){
            $internal_js = array();
        }
        $file = $this->w3GetFullUrlCachePath().'/main_js.json';
        //$this->w3CreateFile($file,json_encode($internal_js));
    }
    function w3StrReplaceSet($str,$rep){
        global $str_replace_str_array, $str_replace_rep_array;
        $str_replace_str_array[] = $str;
        $str_replace_rep_array[] = $rep;        
    }
    
    function w3StrReplaceSetImg($str,$rep){
        global $str_replace_str_img, $str_replace_rep_img;
        $str_replace_str_img[] = $str;
        $str_replace_rep_img[] = $rep;
    }
    function w3_str_replace_bulk_img($html){
        global $str_replace_str_img, $str_replace_rep_img;
        //$this->w3CreateFile($this->w3GetFullUrlCachePath().'/img.json',json_encode(array($str_replace_str_img,$str_replace_rep_img)));
        $html = str_replace($str_replace_str_img,$str_replace_rep_img,$html);
        return $html;
    }

    function w3StrReplaceSetCss($str,$rep,$key=''){
        global $str_replace_str_css, $str_replace_rep_css;
        if($key){
            $str_replace_str_css[$key] = $str;
            $str_replace_rep_css[$key] = $rep;
        }else{
            $str_replace_str_css[] = $str;
            $str_replace_rep_css[] = $rep;
        }
    }
    function w3_str_replace_bulk_css($html){
        global $str_replace_str_css, $str_replace_rep_css;
        //$this->w3CreateFile($this->w3GetFullUrlCachePath().'/css.json',json_encode(array($str_replace_str_css,$str_replace_rep_css)));
        if(!empty($str_replace_rep_css['php'])){
            $str_replace_rep_css['php'] = '<style>'.file_get_contents($str_replace_rep_css['php']).'</style>';
        }
        $html = str_replace($str_replace_str_css,$str_replace_rep_css,$html);
        return $html;
    }

    function w3_str_replace_bulk($html){
        global $str_replace_str_array, $str_replace_rep_array;
        $html = str_replace($str_replace_str_array,$str_replace_rep_array,$html);
        return $html;
    }
    
    function w3_get_wp_cache_path($path=''){
        $cache_path = $this->addSettings['wp_cache_path'].(!empty($path) ? '/'.$path : '');
        $this->w3_check_if_folder_exists($cache_path);
        return $cache_path;
    }
    function w3GetCachePath($path=''){
        $cache_path = $this->addSettings['rootCachePath'].(!empty($path) ? '/'.$path : '');
        $this->w3_check_if_folder_exists($cache_path);
        return $cache_path;
    }
    function w3GetCriticalCachePath($path=''){
        $cache_path = $this->addSettings['criticalCssPath'].(!empty($path) ? '/'.$path : '');
        $this->w3_check_if_folder_exists($cache_path);
        return $cache_path;
    }
    
    function w3GetFullUrlCachePath($full_url=''){
        $full_url = !empty($full_url) ? $full_url : $this->addSettings['fullUrl'];
        $url_array = parse_url($full_url);
        $query = !empty($url_array['query']) ? '/?'.$url_array['query'] : '';
        $full_url_arr = explode('/',trim($url_array['path'],'/').$query);
        $cache_path = $this->w3GetCachePath('all');
        foreach($full_url_arr as $path){
            $cache_path .= '/'.md5($path);
        }
        
        $this->w3_check_if_folder_exists($cache_path);
        return $cache_path;
    }
    function w3_check_if_folder_exists($path){
        
        if(is_dir($path)){
            return $path;
        }
        try {
          mkdir($path,0777,true); 
        }
        catch(Exception $e) {
          echo 'Message: '.$path .$e->getMessage();
        }
        return $path;
    }
    
    function getCurlUrl($url, $curl){		
        $curl->setOption(CURLOPT_HEADER, 0);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $curl->setOption(CURLOPT_CUSTOMREQUEST, 'GET');		
        $curl->addHeader("Content-Type", "application/json");		
        $curl->get($url);		
        $response = $curl->getBody();
        return $response;
    }
    
    function optimize_image($width,$url,$curl,$is_webp = false){
        $key = $this->settings['license_key'];
        $key_activated = 1;
        if(empty($key) || empty($key_activated)){
            return "License key not activated.";
        }
        $width = $width < 1920 ? $width : 1920;
        if($is_webp){
            $q = !empty($this->settings['webp_quality']) ? $this->settings['webp_quality'] : '';
            return $this->getCurlUrl($this->addSettings['w3ApiUrl'].'basic.php?key='.$key.'&width='.$width.'&q='.$q.'&url='.urlencode($url).'&webp=1',$curl);
        }else{
            $q = !empty($this->settings['img_quality']) ? $this->settings['img_quality'] : '';
            return $this->getCurlUrl($this->addSettings['w3ApiUrl'].'basic.php?key='.$key.'&width='.$width.'&q='.$q.'&url='.urlencode($url),$curl);
        }
    }

    function w3CombineGoogleFonts($full_css_url){
        if(empty($this->settings['google_fonts'])){		
            return false;
        }
        
		$url_arr = parse_url(str_replace('#038;','&',$full_css_url));
		if(strpos($url_arr['path'],'css2') !== false){
			$query_arr = explode('&',$url_arr['query']);
			if(!empty($query_arr) && count($query_arr) > 0){
				foreach($query_arr as $family){
					if(strpos($family,'family') !== false){
						$this->addSettings['fonts_api_links_css2'][] = $family;
					}
				}
				return true;
			}
			return false;
		
		}elseif(!empty($url_arr['query'])){
			parse_str($url_arr['query'], $get_array);
			if(!empty($get_array['family'])){
				$font_array = explode('|',$get_array['family']);
				foreach($font_array as $font){
					
					if(!empty($font)){
						$font_split = explode(':',$font);
							
						if(empty($font_split[0])){
							continue;
						}
						if(empty($this->addSettings['fonts_api_links'][$font_split[0]]) || !is_array($this->addSettings['fonts_api_links'][$font_split[0]])){
							$this->addSettings['fonts_api_links'][$font_split[0]] = array();
						}
						$this->addSettings['fonts_api_links'][$font_split[0]] = !empty($font_split[1]) ? array_merge($this->addSettings['fonts_api_links'][$font_split[0]],explode(',',$font_split[1])) : $this->addSettings['fonts_api_links'][$font_split[0]];
					}
				}
				return true;
			}
			return false;
		}
		return false;
    }

    
    function w3_get_tags_data($data,$start_tag,$end_tag){
        $data_exists = 0; $i=0;
        $tag_char_len = strlen($start_tag);
        $end_tag_char_len = strlen($end_tag);
        $script_array = array();
        while($data_exists != -1 && $i<500) {
            $data_exists = strpos($data,$start_tag,$data_exists);
            if(!empty($data_exists)){
                $end_tag_pointer = strpos($data,$end_tag,$data_exists);
                $script_array[] = substr($data, $data_exists, $end_tag_pointer-$data_exists+$end_tag_char_len);
                $data_exists = $end_tag_pointer;
            }else{
                $data_exists = -1;
            }
            $i++;
        }
        return $script_array;
    }
    
    private function w3_cache_rmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object){
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir" && $object != 'critical-css'){
                        $this->w3_cache_rmdir($dir."/".$object);
                    }else{
                      @unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            @unlink($dir);
        }
    }
    private function w3_rmdir($dir) {
        //echo $dir; exit;
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object){
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir"){
                        $this->w3_rmdir($dir."/".$object);
                    }else{
                      @unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            @unlink($dir);
        }
    }

    function w3RemoveCriticalCssCacheFiles($curl) {
        $this->updateDataFile('critical_css_delete_time',date('d:m:Y::h:i:sa'),'no');
        $this->w3_rmdir($this->w3GetCriticalCachePath());
        $this->w3_delete_server_cache($curl);
        $this->updateDataFile('w3speedsterPreloadCss','','no');
        return true;
    
    }
    function w3_delete_server_cache($curl){
        $options = 'url='.$this->addSettings['homeUrl'].'&'.'key='.$this->settings['license_key'].'&'.'_wpnonce='.'435erer';
        $response = $this->getCurlUrl($this->addSettings['w3ApiUrl'].'css/delete-css.php?'.$options,$curl);
        return true;
        
    }
    
	function w3_setAllLinks($data,$resources=array()){
        $resource_arr = array();
        $comment_tag = $this->w3_get_tags_data($data,'<!--','-->');
        $data = str_replace($comment_tag,'',$data);
        if(!empty($this->settings['js']) && $this->settings['js'] == 1 && in_array('script',$resources)){
            $resource_arr['script'] = $this->w3_get_tags_data($data,'<script','</script>');
               $data = str_replace($resource_arr['script'],'',$data);
        }else{
            $resource_arr['script'] = array();
        }
        
        if( in_array('img',$resources) ){
            $resource_arr['img'] = $this->w3_get_tags_data($data,'<img','>');
        }else{
            $resource_arr['img'] = array();
        }
        if(!empty($this->settings['css']) && $this->settings['css'] == 1 && in_array('link',$resources) ){
            $resource_arr['link'] = $this->w3_get_tags_data(str_replace('<style','<link',$data),'<link','>');
        }else{
            $resource_arr['link'] = array();
        }       
        if(in_array('iframe',$resources)){
            $resource_arr['iframe'] = $this->w3_get_tags_data($data,'<iframe','>');
        }else{
            $resource_arr['iframe'] = array();
        }
        if(in_array('video',$resources)){
            $resource_arr['video'] = $this->w3_get_tags_data($data,'<video','</video>');
        }else{
            $resource_arr['video'] = array();
        }
        if(in_array('url',$resources)){
            $resource_arr['url'] = $this->w3_get_tags_data($data,'url(',')');
        }else{
            $resource_arr['url'] = array();
        }
        return $resource_arr;
    }

    function w3_get_cache_file_size(){
        $dir = $this->w3GetCachePath();
        $size = 0;
        foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
            $size += is_file($each) ? filesize($each) : $this->w3_folderSize($each);
        }
        return ($size / 1024) / 1024;
    }
        
    function w3_foldersize($path) {
        $total_size = 0;
        $files = scandir($path);
        $cleanPath = rtrim($path, '/'). '/';
        foreach($files as $t) {
            if ($t<>"." && $t<>"..") {
                $currentFile = $cleanPath . $t;
                if (is_dir($currentFile)) {
                    $size = $this->w3_foldersize($currentFile);
                    $total_size += $size;
                }
                else {
                    $size = filesize($currentFile);
                    $total_size += $size;
                }
            }   
        }
        return $total_size;
    }
    function w3_cache_size_callback() {
        $filesize = $this->w3_get_cache_file_size();
        $this->updateDataFile('w3_speedup_filesize',$filesize,true);
        return $filesize;
    }
    function w3_create_random_key(){
        $this->updateDataFile('w3_rand_key',rand(10,1000),0);
    }
    
    function w3_get_pointer_to_inject_files($html){
        global $appendonstyle;
        if(!empty($appendonstyle)){
            return $appendonstyle;
        }

        $start_body_pointer = strpos($html,'<body');
        $start_body_pointer = $start_body_pointer ? $start_body_pointer : strpos($html,'</head');
        $head_html = substr($html,0,$start_body_pointer);
        $comment_tag = $this->w3_get_tags_data($head_html,'<!--','-->');
        foreach($comment_tag as $comment){
            $head_html = str_replace($comment,'',$head_html);
        }        
        if(strpos($head_html,'<style') !== false){
            $appendonstyle=1;
        }elseif(strpos($head_html,'<link') !== false){
            $appendonstyle=2;
        }else{
            $appendonstyle=3;
        }
        return $appendonstyle;
    }

    function w3CheckIfPageExcluded($exclude_setting){        
        $e_p_from_optimization = !empty($exclude_setting) ? explode("\r\n",$exclude_setting) : array();        
        if(!empty($e_p_from_optimization)){
            $testing = $this->request->getParam('testing');
			foreach( $e_p_from_optimization as $e_page ){
                if(empty($e_page)){
                    continue;
                }
                if(empty($testing) && $this->addSettings['homeUrl'] == $e_page){
                    return true;
                }else if($this->addSettings['homeUrl'] != $e_page){
                    if(strpos($this->addSettings['fullUrl'], $e_page)!==false){
                        return true;
                    }
                }
            }			
        }
        return false;
    }
	function w3_preload_resources(){		
		$preload_html = '';
		$preload_resources = !empty($this->settings['preload_resources']) ? explode("\r\n", $this->settings['preload_resources']) : array();
		
		if(!empty($preload_resources)){
			foreach($preload_resources as $link){
				$link_arr = explode('?',$link);
				$extension = explode(".", $link_arr[0]);
				$extension = end($extension);
				if(empty($extension)){
					continue;
				}
				if(in_array($extension, array('jpeg','jpg','png','gif','webp','tiff', 'psd', 'raw', 'bmp', 'heif', 'indd'))){
					$preload_html .= '<link rel="preload" href="'.$link.'" as="image"/>';
				}
				if(in_array(strtolower($extension), array('otf','ttf','woff','woff2','gtf','mmm', 'pea', 'tpf', 'ttc', 'wtf'))){
					$preload_html .= '<link rel="preload" href="'.$link.'" as="font" type="font/'.$extension.'" crossorigin>';
				}
				
				if(in_array($extension, array('mp4','webm'))){
					$preload_html .= '<link rel="preload" href="'.$link.'" as="video" type="video/'.$extension.'">';
				}
				if($extension == 'css'){
					$preload_html .= '<link rel="preload" href="'.$link.'" as="style">';
				}
				if($extension == 'js'){
					$preload_html .= '<link rel="preload" href="'.$link.'" as="script">';
				}				
			}
		}
		return $preload_html;
	}
}
