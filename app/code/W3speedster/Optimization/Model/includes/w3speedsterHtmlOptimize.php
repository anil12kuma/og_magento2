<?php
namespace W3speedster\Optimization\Model\includes;

require_once('w3speedsterJs.php');
class w3speedsterHtmlOptimize extends w3speedsterJs{
    protected $_curl;
    public $hooks;
     public function __construct($settings,$hooks,$dir,$req){
        $this->hooks = $hooks;
        parent::__construct($settings,$hooks,$dir,$req);
    }
    function w3Speedup($html){
        global $hooks;
		$customerSession = $this->objectManager->get('Magento\Customer\Model\Session');
        if($this->w3NoOptimization($html,$customerSession->isLoggedIn())){
            return $html;
        }
		if(isset($this->hooks['customize_add_settings'])){
            eval($this->hooks['customize_add_settings']);
        } 
        
        $this->addSettings['disable_htaccess_webp'] = $this->w3DisableHtaccessWepb();
        $html = $this->w3CustomJsEnqueue($html);
        $html = str_replace(array('<script type="text/javascript"',"<script type='text/javascript'",'<style type="text/css"',"<style type='text/css'"),array('<script ','<script ','<style ','<style '),$html);
        
        if(isset($this->hooks['before_optimization'])){
            eval($this->hooks['before_optimization']);
        }
        
        $html = $this->w3DebugTime($html,'before create all links');
		$all_links = $this->w3_setAllLinks($html,array('script','link','iframe','video','img','url'));
		$html = $this->w3DebugTime($html,'after create all links');
		$html = $this->minify($html, $all_links['script']);
		$html = $this->w3DebugTime($html,'minify script');
	   
		$html = $this->lazyload($html, array('iframe'=>$all_links['iframe'],'video'=>$all_links['video'],'img'=>$all_links['img'],'url'=>$all_links['url'] ) );
		$html = $this->w3DebugTime($html,'lazyload images');
		$html = $this->minifyCss($html,$all_links['link']);
		$html = $this->w3DebugTime($html,'minify css');
		$ignore_critical_css = 0;
		
		if($customerSession->isLoggedIn()) {
			$ignore_critical_css = 1;
		}
		if(function_exists('w3_no_critical_css')){
			 if(w3_no_critical_css($this->addSettings['fullUrl'])){
				$ignore_critical_css = 1; 
			 }
		}
	
		if(!empty($this->request->getParam('w3_get_css_post_type'))){
			$html .= 'rocket22'.str_replace($this->addSettings['documentRoot'],'',$this->w3PreloadCssPath()).'--'.$this->addSettings['critical_css'];
		}
		
		
		if(!empty($this->settings['load_critical_css']) && !$ignore_critical_css){
			if(!is_file($this->w3PreloadCssPath().'/'.$this->addSettings['critical_css'])){
				$this->w3AddPageCriticalCss();
			}else{
				$critical_css = file_get_contents($this->w3PreloadCssPath().'/'.$this->addSettings['critical_css']);
				if(!empty($critical_css)){
					$html = $this->w3InsertContentHead($html , '{{main_w3_critical_css}}',3);
					if(isset($this->hooks['customize_critical_css'])){
						eval($this->hooks['customize_critical_css']);
					}
					$this->w3StrReplaceSetCss('{{main_w3_critical_css}}','<style>'.$critical_css.'</style>');
				}else{
					$this->w3AddPageCriticalCss();
				}
			}
		}
		$html = $this->w3_str_replace_bulk($html);
		$html = $this->w3_str_replace_bulk_img($html);
		$html = $this->w3_str_replace_bulk_css($html);
		$html = $this->w3InsertContentHead($html , '<script>'.$this->w3_lazy_load_javascript().'</script>',3);
		$html = $this->w3InsertContentHead($html , $this->w3LoadGoogleFonts(),3);
		$html = $this->w3DebugTime($html,'replace json');
		$this->w3InsertContentHeadInJson();
		$this->w3_main_css_url_to_json();
        
        $preload_html = $this->w3_preload_resources();
		$html = $this->w3InsertContentHead($html , $preload_html,3);
        $position = strrpos($html,'</body>');
        $html =	substr_replace( $html, '<script>'.$this->w3_lazy_load_images().'</script>', $position, 0 );
        $html = $this->w3DebugTime($html,'w3 script');
        if(isset($this->hooks['after_optimization'])){
            eval($this->hooks['after_optimization']);
        }	
        
        $html = $this->w3DebugTime($html,'before final output');
        return $html;
    } 
	function w3AddPageCriticalCss(){
		if(!empty($this->settings['optimization_on'])){
			$preload_css = $this->getDataFile('w3speedsterPreloadCss');
			$preload_css = !empty($preload_css) && $preload_css != 'A' ? $preload_css : array();
			if(is_array($preload_css) && count($preload_css) > 20){
				return;
			}
			if(!array_key_exists(base64_encode($this->addSettings['fullUrlWithoutParam']),$preload_css) || (!empty($preload_css[$this->addSettings['fullUrlWithoutParam']]) && $preload_css[$this->addSettings['fullUrlWithoutParam']][0] != $this->addSettings['critical_css']) ){
				$preload_css[base64_encode($this->addSettings['fullUrlWithoutParam'])] = array($this->addSettings['critical_css'],2,$this->w3PreloadCssPath());
				$this->updateDataFile('w3speedsterPreloadCss',$preload_css,1);
				$this->updateDataFile('w3speedsterPreloadCssTotal',count($this->getDataFile('w3speedsterPreloadCss',1)),0);
				return $this->getDataFile('w3speedsterPreloadCss');
			}
		}
	}
    function w3LoadGoogleFonts(){
		$google_font = array();
        if(!empty($this->addSettings['fonts_api_links'])){
            $all_links = '';
            foreach($this->addSettings['fonts_api_links'] as $key => $links){
                $all_links .= !empty($links) && is_array($links) ? $key.':'.implode(',',$links).'|' : $key.'|';
            }
            $google_font[] = $this->addSettings['secure']."fonts.googleapis.com/css?display=swap&family=".urlencode(trim($all_links,'|'));
        }
		if(!empty($this->addSettings['fonts_api_links_css2'])){
			$all_links = 'https://fonts.googleapis.com/css2?';
			foreach($this->addSettings['fonts_api_links_css2'] as $font){
				$all_links .= $font.'&';
			}
			$all_links .= 'display=swap';
			$google_font[] = $all_links;
		}
		return '<script>var w3_googlefont='.json_encode($google_font).';</script>';
	}
    public function w3HeaderCheck() {
        return $this->isSpecialContentType()
            || $this->isSpecialRoute()
            || $_SERVER['REQUEST_METHOD'] === 'POST'
            || $_SERVER['REQUEST_METHOD'] === 'PUT'
            || $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }

    private function isSpecialContentType() {
        if(isset($this->addSettings['headers']['Accept']) ) {
            $contentType = explode(',',$this->addSettings['headers']['Accept']);

            if( is_array($contentType) ) {
                foreach( $contentType as $name => $value ) {
                    if( $value == "application/json" ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function isSpecialRoute() {
        $current_url = $this->addSettings['fullUrl'];
        return false;
        if( preg_match('/(.*\/wp\/v2\/.*)/', $current_url) ) {
            return true;
        }

        if( preg_match('/(.*wp-login.*)/', $current_url) ) {
            return true;
        }

        if( preg_match('/(.*wp-admin.*)/', $current_url) ) {
            return true;
        }

        return false;
    }
    function w3CustomJsEnqueue($html){
        if(!empty($this->settings['custom_js'])){
            $custom_js = stripslashes($this->settings['custom_js']);
        }else{
            $custom_js = 'console.log("js loaded");';
        }
        $js_file_name1 = 'custom_js_after_load.js';
        if(!is_file($this->w3GetCachePath('js').'/'.$js_file_name1)){
            $this->w3CreateFile($this->w3GetCachePath('js').'/'.$js_file_name1, $custom_js);
        }
        $html = $this->w3StrReplaceLast('</body>','<script src="'.$this->addSettings['cacheUrl'].'/js/'.$js_file_name1.'"></script></body>',$html);
        return $html;
        
    }
    function w3NoOptimization($html,$customer){
        
        if(function_exists('w3speedster_exclude_page_optimization')){
            return w3speedster_exclude_page_optimization($html);
        }
        
        if(!empty($this->request->getParam('orgurl')) || strpos($html,'<body') === false || strpos($html,'<?xml version=') !== false){
            return true;
        }
        
        if($this->w3HeaderCheck()){
            return true;
        }		
        
        if(empty($this->settings['optimization_on']) && empty($this->request->getParam('tester')) && empty($this->request->getParam('testing'))){
             return true;
        }
        if(empty($this->settings['optimize_user_logged_in']) && $customer){
			return true;
		}
		if(empty($this->settings['optimize_query_parameters']) && $this->addSettings['fullUrl'] != $this->addSettings['fullUrlWithoutParam'] && empty($this->request->getParam('tester'))){
			return true;
		}
        if($this->w3CheckIfPageExcluded($this->settings['exclude_pages_from_optimization'])){
            return true;
        }
        
        if(!empty($this->request->getParam('testing'))){
            return false;
        }
        return false;
    }
    function start($html,$curl){
        $this->_curl = $curl;
                
        return $this->w3Speedup($html);	
        
    }
    
    function lazyload($html, $all_links){
        $excluded_img = !empty($this->settings['exclude_lazy_load']) ? explode("\r\n",stripslashes($this->settings['exclude_lazy_load'])) : array();
        if(!empty($this->settings['lazy_load_iframe'])){
            $iframe_links = $all_links['iframe'];
            foreach($iframe_links as $img){
                if(strpos($img,'\\') !== false){
					continue;
				}
                $exclude_image = 0;
                foreach( $excluded_img as $ex_img ){
                    if(!empty($ex_img) && strpos($img,$ex_img)!==false){
                        $exclude_image = 1;
                    }
                }
                if($exclude_image){
                    continue;
                }
                $img_obj = $this->w3ParseLink('iframe',$img);
				$iframe_html = '';
                if(strpos($img_obj['src'],'youtu') !== false){
                    preg_match("#([\/|\?|&]vi?[\/|=]|youtu\.be\/|embed\/)([a-zA-Z0-9_-]+)#", $img_obj['src'], $matches);
                    if(empty($img_obj['style'])){
                        $img_obj['style'] = '';
                    }
                    $img_obj['style'] .= 'background-image:url(https://i.ytimg.com/vi/'.trim(end($matches)).'/sddefault.jpg)';
					//$iframe_html = '<img width="68" height="48" class="iframe-img" src="/wp-content/uploads/yt-png2.png"/>';
                }
                $img_obj['data-src'] = $img_obj['src'];
                $img_obj['src'] = 'about:blank';
                $img_obj['data-class'] = 'LazyLoad';
                $this->w3StrReplaceSetImg($img,$this->w3ImplodeLinkArray('iframelazy',$img_obj));
            }
        }
        if(!empty($this->settings['lazy_load_video'])){
            $iframe_links = $all_links['video'];
            if(strpos($this->addSettings['upload_base_url'],$this->addSettings['homeUrl']) !== false){
                $v_src = $this->addSettings['imageHomeUrl'].str_replace($this->addSettings['homeUrl'],'',$this->addSettings['upload_base_url']).'/blank.mp4';
            }else{
                $v_src = $this->addSettings['upload_base_url'].'/blank.mp4';
            }
            foreach($iframe_links as $img){
                if(strpos($img,'\\') !== false){
                    continue;
                }
                $exclude_image = 0;
                foreach( $excluded_img as $ex_img ){
                    if(!empty($ex_img) && strpos($img,$ex_img)!==false){
                        $exclude_image = 1;
                    }
                }
                if($exclude_image){
                    continue;
                }
                
                $img_new = str_replace('src=','data-class="LazyLoad" src="'.$v_src.'" data-src=',$img);
                $this->w3StrReplaceSetImg($img,$img_new);
            }
        }
        $img_links = $all_links['img'];
        if(!empty($all_links['img'])){
            $lazy_load_img = !empty($this->settings['lazy_load']) ? 1 : 0;
            $enable_cdn = 0;
            if($this->addSettings['imageHomeUrl'] != $this->addSettings['homeUrl'] ){
                $enable_cdn = 1;
            }
            $exclude_cdn_arr = !empty($this->addSettings['exclude_cdn']) ? $this->addSettings['exclude_cdn'] : array();
                       
            $webp_enable = $this->addSettings['webp_enable'];
            $webp_enable_instance = $this->addSettings['webp_enable_instance'];
            $webp_enable_instance_replace = $this->addSettings['webp_enable_instance_replace'];
            foreach($img_links as $img){
                if(strpos($img,'\\') !== false){
                    continue;
                }
				$blank_image_url = $this->addSettings['upload_base_url'].'/cache';
				
                $exclude_image = 0;
                $imgnn = $img;
                $imgnn_arr = $this->w3ParseLink('img',str_replace($this->addSettings['imageHomeUrl'],$this->addSettings['homeUrl'],$imgnn));
                if(empty($imgnn_arr['src'])){
                    continue;
                }
                if(strpos($imgnn_arr['src'],'?') !== false){
                    $temp_src = explode('?',$imgnn_arr['src']);
                    $imgnn_arr['src'] = $temp_src[0];
                }
                if(strpos($imgnn_arr['src'],$_SERVER['HTTP_HOST']) === false && !$this->w3IsExternal($imgnn_arr['src'])){
                    $imgnn_arr['src'] = $this->addSettings['homeUrl'].'/'.ltrim($imgnn_arr['src'],'/');
                    $imgnn = $this->w3ImplodeLinkArray('img',$imgnn_arr);
                }
                $w3_img_ext = '.'.pathinfo($imgnn_arr['src'], PATHINFO_EXTENSION);
                $imgsrc_filepath = str_replace($this->addSettings['upload_base_url'],$this->addSettings['upload_base_dir'],$imgnn_arr['src']);
                $imgsrc_webpfilepath = str_replace($this->addSettings['uploadPath'],$this->addSettings['webp_upload_path'],$imgsrc_filepath).$this->addSettings['w3_extension'];
                if($enable_cdn){
                    $image_home_url = $this->addSettings['imageHomeUrl'];
                    foreach($exclude_cdn_arr as $cdn){
                        if(strpos($img,$cdn) !== false){
                            $image_home_url = $this->addSettings['homeUrl'];
                            break;
                        }
                    }
                    $imgnn = str_replace($this->addSettings['homeUrl'],$image_home_url,$imgnn);
                }else{
                    $image_home_url = $this->addSettings['homeUrl'];
                }
                
                if(strpos($img, ' srcset=') === false){
                    $pppp = explode("/",$imgsrc_filepath);
                    $cleanTags= preg_grep("/version/", $pppp, PREG_GREP_INVERT);
                    $imgsrc_filepath = implode("/",$cleanTags);
                    $img_size = !$this->w3IsExternal($imgnn_arr['src']) && is_file($imgsrc_filepath) ? @getimagesize($imgsrc_filepath) : array();
					if(!empty($img_size[0]) && !empty($img_size[1])){
						if(empty($imgnn_arr['width']) || $imgnn_arr['width'] == 'auto'){
							$imgnn = str_replace(array(' width="auto"',' src='),array('',' width="'.$img_size[0].'" src='),$imgnn);
						}
						if(empty($imgnn_arr['height']) || $imgnn_arr['height'] == 'auto'){
							$imgnn = str_replace(array(' height="auto"',' src='),array('',' height="'.$img_size[1].'" src='),$imgnn);
						}
						if((int)$img_size[0]/(int)$img_size[1] > 1.9){
							$blank_image_url .= '/blank-h.png';
						}elseif((int)$img_size[0]/(int)$img_size[1] > 1.3){
							$blank_image_url .= '/blank-3x4.png';
						}elseif((int)$img_size[0]/(int)$img_size[1] >= 1){
							$blank_image_url .= '/blank-square.png';
						}elseif((int)$img_size[0]/(int)$img_size[1] >= .75){
							$blank_image_url .= '/blank-4x3.png';
						}elseif((int)$img_size[0]/(int)$img_size[1] < .75){
							$blank_image_url .= '/blank-p.png';
						}else{
							$blank_image_url .= '/blank-square.png';
						}
					}else{
						$blank_image_url .= '/blank-square.png';
					}
                }
                /*if(count($webp_enable) > 0 && in_array($w3_img_ext, $webp_enable)){					
                    if(is_file($imgsrc_webpfilepath)){
						$imgnn = str_replace($webp_enable_instance,$webp_enable_instance_replace,$imgnn);
                    }
                }*/
                
                if($lazy_load_img){
                    foreach( $excluded_img as $ex_img ){
                        if(!empty($ex_img) && strpos($img,$ex_img)!==false){
                            $exclude_image = 1;
                        }
                    }
                }else{
                    $exclude_image = 1;
                }
                
                if($exclude_image){
                    if($img != $imgnn){
                        $this->w3StrReplaceSetImg($img,$imgnn);
                    }
                    continue;
                }
                
                if(strpos($imgnn, ' srcset=') !== false){
                    $imgnn = str_replace(' srcset=',' data-srcset=',$imgnn);
                }
                $imgnn = str_replace(' src=',' data-class="LazyLoad" src="'. $blank_image_url .'" data-src=',$imgnn);
                $this->w3StrReplaceSetImg($img,$imgnn);
            }
        }
       
        $html = $this->w3ConvertArrRelativeToAbsolute($html, $this->addSettings['homeUrl'].'/index.php',$all_links['url']);
        return $html;
    }
    public function w3DisableHtaccessWepb(){
        if( !empty($_SERVER['HTTP_ACCEPT']) && strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) == true ) {
            return true;
        }
        return false;
    }
    
}