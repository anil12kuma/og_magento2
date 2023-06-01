<?php
namespace W3speedster\Optimization\Model\includes;

require_once('w3speedsterCss.php');
class w3speedsterJs extends w3speedsterCss{
    public function __construct($settings,$hooks,$dir,$req){
        parent::__construct($settings,$hooks,$dir,$req); 
    }
    function w3ParseScript($tag,$link){
        $data_exists = strpos($link,'>');
        if(!empty($data_exists)){
            $end_tag_pointer = strpos($link,'</script>',$data_exists);
            $link_arr = substr($link, $data_exists+1, $end_tag_pointer-$data_exists-1);
        }
        return $link_arr;
   }

    function w3CreateFileCacheJs($path){
        $cache_file_path = $this->w3GetCachePath('js').'/'.md5($this->addSettings['w3_rand_key'].$path).'.js';
        if( !file_exists($cache_file_path) ){            
            $html = file_get_contents($this->addSettings['documentRoot'].$path);
            $src_array = explode('/',$path);
            $count = count($src_array);
            unset($src_array[$count-1]);
			if(strpos($path,'require.js') !== false || strpos($path,'require.min.js') !== false){
				$html = str_replace(array('fn,4','fn, 4'),array('fn,20','fn, 20'),$html);
			}
            if(!empty($this->settings[base64_decode('aXNfYWN0aXZhdGVk')])){
				if(strpos($html,'jQuery requires a window with a document') !== false && empty($this->addSettings['holdready'])){
                    $html .= ';if(typeof($) == "undefined"){$ = jQuery;}else{var $ = jQuery;} jQuery.holdReady( true );';
                    $this->addSettings['holdready'] = 1;
                }
                if(strpos($html,'jQuery requires a window with a document') === false){
                    $html = preg_replace('/([\s\;\}\,\)\(\{])([a-zA-Z]+.addEventListener\s*\(\s*[\'|"]\s*DOMContentLoaded\s*[\'|"]\s*,)/', "$1setTimeout(", $html);
                    $html = preg_replace('/([\s\;\}\,\)\(\{])([a-zA-Z]+.addEventListener\s*\(\s*[\'|"]\s*load\s*[\'|"]\s*,)/', "$1setTimeout(", $html);
                    
                }
                if( !empty($this->addSettings['jquery_excluded']) && empty($this->addSettings['holdready'])){
                    $html = (function_exists('w3speedster_load_2_jquery') ? file_get_contents($this->addSettings['jquery_excluded']) : '').';;if(typeof($) != "undefined"){$ = jQuery;}else{var $ = jQuery;}jQuery.holdReady( true );'.$html;
                    $this->addSettings['holdready'] = 1;
                }
                if(strpos(trim($html),'"use strict";') === 0){
                    $html = preg_replace('/"use strict";/', '', $html, 1);
                }
                
                if(strpos($path,'custom_js_after_load.js') && !empty($this->addSettings['holdready'])){
                    $html = ';jQuery.holdReady( false );'.$html;
                }
            }
            
            if(isset($this->hooks['internal_js_minify'])){
                eval($this->hooks['internal_js_minify']);
            }          
          
            $html = str_replace('sourceMappingURL=','sourceMappingURL='.implode('/',$src_array),$html.";\n");
            $this->w3CreateFile($cache_file_path, $html );
        }
        return str_replace($this->addSettings['documentRoot'],'',$cache_file_path);
    }
    
    function w3CompressJs($html){      
        return $html;
    }
    function minifyCss($html,$all_links){
       return parent::minifyCss($html,$all_links);
    }
    
    function minify($html, $script_links){
        if(!empty($this->settings['exclude_page_from_load_combined_js']) && $this->w3CheckIfPageExcluded($this->settings['exclude_page_from_load_combined_js'])){
			return $html;
        }
        if(isset($this->hooks['exclude_page_js'])){
            eval($this->hooks['exclude_page_js']);
        }
        
        if(!empty($script_links) && $this->settings['js'] == 1){			
            $lazy_load_js = !empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' ? 1 : 0;
            $force_innerjs_to_lazy_load  = !empty($this->settings['force_lazy_load_inner_javascript']) ? explode("\r\n", $this->settings['force_lazy_load_inner_javascript']) : array();
            $exclude_js_arr_split  = !empty($this->settings['exclude_javascript']) ? explode("\r\n", $this->settings['exclude_javascript']) : array();
            foreach($exclude_js_arr_split as $key => $value){
				if(strpos($value,' defer') !== false){
					$exclude_js_arr[$key]['string'] = str_replace(' defer','',$value);
					$exclude_js_arr[$key]['defer'] = 1;
				}elseif(strpos($value,' full') !== false){
					$exclude_js_arr[$key]['string'] = str_replace(' full','',$value);
					$exclude_js_arr[$key]['full'] = 1;
				}else{	
					$exclude_js_arr[$key]['string'] = $value;
					$exclude_js_arr[$key]['defer'] = 0;
				}
			}
            $exclude_inner_js= !empty($this->settings['exclude_inner_javascript']) ? explode("\r\n", stripslashes($this->settings['exclude_inner_javascript'])) : array('google-analytics', 'hbspt',base64_decode("LyogPCFbQ0RBVEFbICov"));
            $load_ext_js_before_internal_js = !empty($this->settings['load_external_before_internal']) ? explode("\r\n", $this->settings['load_external_before_internal']) : array();
            $all_js='';
            $included_js = array();
            $final_merge_js = array();
            $js_file_name = '';
            $enable_cdn = 0;
            if($this->addSettings['imageHomeUrl'] != $this->addSettings['homeUrl']){
                $ext = '.js';
                if(empty($this->addSettings['exclude_cdn']) || !in_array($ext,$this->addSettings['exclude_cdn'])){
                    $enable_cdn = 1;
                }
            }
            
            $final_merge_has_js = array();
            for($si=0; $si < count($script_links); $si++){
                $script = $script_links[$si];
				$script_obj = !empty($this->addSettings['script_obj'][$si]) ? $this->addSettings['script_obj'][$si] : $this->w3ParseLink('script',str_replace($this->addSettings['imageHomeUrl'],$this->addSettings['homeUrl'],$script_links[$si]));
				$script_text = '';
				if(!array_key_exists('src',$script_obj)){
                    $script_text = $this->w3ParseScript('<script',$script);
                }
                if(!empty($script_obj['type']) && strtolower($script_obj['type']) != 'application/javascript' && strtolower($script_obj['type']) != 'text/javascript' && strtolower($script_obj['type']) != 'text/jsx;harmony=true'){
                    continue;
                }

                if(!empty($script_obj['src'])){					
                    $url_array = $this->w3ParseUrl($script_obj['src']);
					if(strpos($url_array['path'],'/version') !== false){
						$url_array['path'] = preg_replace('/version(\d)*\//', '', $url_array['path']);
					}
                    $exclude_js = 0;
                    if(!empty($exclude_js_arr) && is_array($exclude_js_arr)){
						foreach($exclude_js_arr as $ex_js){
							if(strpos($script,$ex_js['string']) !== false){
								if(!empty($ex_js['defer'])){
									$exclude_js = 2;
								}elseif(!empty($ex_js['full'])){
									$exclude_js = 3;
								}else{
									$exclude_js = 1;
								}
							}
						}
					}
                    if(function_exists('w3speedster_exclude_javascript_filter')){
                        $exclude_js = w3speedster_exclude_javascript_filter($exclude_js,$script_obj,$script);
                    }
                    if(!$this->w3IsExternal($script_obj['src']) && $this->w3Endswith($url_array['path'], '.js') && $exclude_js != 3){
                        $old_path = $url_array['path'];
                        if(file_exists($this->addSettings['documentRoot'].$url_array['path'])){
                            $url_array['path'] = $this->w3CreateFileCacheJs($url_array['path']);
                        }else{
                            $url_array['path'] = $this->w3CreateFileCacheJs_url($script_obj['src']);
                        }
                        $script_obj['src'] = $this->addSettings['homeUrl'].$url_array['path'];
                    }
                    if($exclude_js){
                        if( $exclude_js == 3){
							$script_obj['src'] = $enable_cdn ? str_replace($this->addSettings['homeUrl'],$this->addSettings['imageHomeUrl'] ,$script_obj['src']) : $script_obj['src'];
							$this->addSettings['preload_resources'][] = $script_obj['src'];
							$this->w3StrReplaceSet($script,$this->w3ImplodeLinkArray('script',$script_obj));
							continue;
						}
						if( $exclude_js == 2){
                            $script_obj['defer'] = 'defer';
						}
						if(file_exists($this->addSettings['documentRoot'].$url_array['path']) && strpos(file_get_contents($this->addSettings['documentRoot'].$url_array['path']),'jQuery requires a window with a document') !== false){
							$this->addSettings['jquery_excluded'] = $this->addSettings['documentRoot'].$url_array['path'];
						}
						$script_obj['src'] = $enable_cdn ? str_replace($this->addSettings['homeUrl'],$this->addSettings['imageHomeUrl'] ,$script_obj['src']) : $script_obj['src'];
						if(!empty($exclude_js) && $exclude_js == 1){
							$this->addSettings['preload_resources'][] = $script_obj['src'];
						}
						$this->w3StrReplaceSet($script,$this->w3ImplodeLinkArray('script',$script_obj));
                        continue;
                    }
                    $exclude_js_bool=0;
					if(!empty($force_innerjs_to_lazy_load)){
                        foreach($force_innerjs_to_lazy_load as $js){
                            if( !empty($js) && strpos($script,$js) !== false){
                                $exclude_js_bool=1;
                                break;
                            }
                        }
                    }
					
                    $val = $script_obj['src'];
                    if(!empty($val) && !$this->w3IsExternal($val) && strpos($script, '.js') && empty($exclude_js_bool)){
                        $final_merge_js[] = $url_array['path'];
						$final_merge_has_js[] = $script;
						if(!empty($final_merge_js) && count($final_merge_js) > 0){
							$cache_js_url = $this->w3CreateJsCombinedCacheFile($final_merge_js, $enable_cdn);
							$this->w3ReplaceJsFilesWithCombinedFiles($final_merge_has_js,$cache_js_url);
							$final_merge_js = array();
							$final_merge_has_js = array();
						}
					}elseif($this->w3IsExternal($val) && empty($exclude_js_bool) ){
						$script_obj['type'] = 'lazyload_int';
						$script_obj['data-src'] = $script_obj['src'];
						unset($script_obj['src']);
						$this->w3StrReplaceSet($script,$this->w3ImplodeLinkArray('script',$script_obj));
					}elseif($exclude_js_bool){
						$script_obj['data-src'] = $enable_cdn ? str_replace($this->addSettings['homeUrl'],$this->addSettings['imageHomeUrl'] ,$script_obj['src']) : $script_obj['src'];
						unset($script_obj['src']);
						$script_obj['type'] = 'lazyload_ext';
						if(function_exists('w3_external_javascript_customize')){
							$script_obj = w3_external_javascript_customize($script_obj, $script);
						}
						$this->w3StrReplaceSet($script,$this->w3ImplodeLinkArray('script',$script_obj));
                    }
                }else{
                    $inner_js = $script_text;
                    $lazy_loadjs = 0;
                    $exclude_js_bool = 0;
					$force_js_bool = 0;
                    $exclude_js_bool = $this->w3CheckJsIfExcluded($inner_js, $exclude_inner_js);
					if(isset($this->hooks['inner_js_customize'])){
                        eval($this->hooks['inner_js_customize']);
                    }
                    if(!empty($force_innerjs_to_lazy_load)){
                        foreach($force_innerjs_to_lazy_load as $js){
                            if(strpos($script_text,$js) !== false){
                                $exclude_js_bool=0;
								$force_js_bool = 1;
                                break;
                            }
                        }
                    }
                     if(!empty($exclude_js_bool) && $exclude_js_bool != 2){
						if(isset($this->hooks['inner_js_customize'])){
							eval($this->hooks['inner_js_customize']);
						}
                        $this->w3StrReplaceSet($script,'<script>'.$script_text.'</script>');
                    }else{
                        
                       if($exclude_js_bool == 2){
							$script_modified = '<script type="lazyload_int" ';
						}elseif($force_js_bool){
    						$script_modified = '<script type="lazyload_ext" ';
    					}else{
    						$script_modified = '<script type="lazyload_int" ';
    					}
                        foreach($script_obj as $key => $value){
                            if($key != 'type'){
                                $script_modified .= $key.'="'.$value.'"';
                            }
                        }
						$script_modified = $script_modified.'>'.$script_text.'</script>';
                        $this->w3StrReplaceSet($script,$script_modified);
                    }
                }
                if($si == count($script_links)-1 && !empty($final_merge_has_js)){
                    if(!empty($final_merge_js) && count($final_merge_js) > 0){
                        $cache_js_url = $this->w3CreateJsCombinedCacheFile($final_merge_js, $enable_cdn);
                        $this->w3ReplaceJsFilesWithCombinedFiles($final_merge_has_js, $cache_js_url);
                        $last_js_url = $cache_js_url;
                        $final_merge_js = array();
                    }
                }
            }
        }
        if(!empty($this->settings['custom_javascript'])){
           if($this->settings['custom_javascript_file'] == 1){    
                $custom_js_path = $this->w3GetCachePath('all-js').'/wnw-custom-js.js';
                if(!is_file($custom_js_path)){
                    $this->w3CreateFile($custom_js_path, stripslashes($this->settings['custom_javascript']));
                }
                $custom_js_url = $this->addSettings['cacheUrl'].'/all-js/wnw-custom-js.js';
                $custom_js_url = $enable_cdn ? str_replace($this->addSettings['homeUrl'],$this->addSettings['imageHomeUrl'] ,$custom_js_url) : $custom_js_url;
                $position = strrpos($html,'</body>');
                $html = substr_replace( $html, '<script '.(!empty($this->settings['custom_javascript_defer']) ? 'defer="defer"' : '').' id="wnw-custom-js" src="'.$custom_js_url.'?ver='.rand(10,1000).'"></script>', $position, 0 );
            }else{
                $position = strrpos($html,'</body>');
                $html = substr_replace( $html, '<script>'.stripslashes($this->settings['custom_javascript']).'</script>', $position, 0 ); 
            }
        }
        
        return $html;
    }
    function w3CheckJsIfExcluded($inner_js, $exclude_inner_js){
		
		$exclude_js_bool=0;
		if(strpos($inner_js,'require.config(') !== false || strpos($inner_js,'require (') !== false || strpos($inner_js,'require(') !== false){
			$exclude_js_bool=2;
		}
		if(strpos($inner_js,'var BASE_URL') !== false){
			$exclude_js_bool=1;
		}
		if(!empty($exclude_inner_js)){
			foreach($exclude_inner_js as $js){
				if(strpos($inner_js,$js) !== false){
					return 1;
					break;
				}
			}
		}
		if(isset($this->hooks['inner_js_exclude'])){
			eval($this->hooks['inner_js_exclude']);
		}
		return $exclude_js_bool;
	}
    function w3ReplaceJsFilesWithCombinedFiles($final_merge_has_js,$cache_js_url){
        if(!empty($final_merge_has_js)){
            $lazy_load_js = !empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' ? 1 : 0;
            for($ii = 0; $ii < count($final_merge_has_js); $ii++){
                if($ii == count($final_merge_has_js) -1 ){
                    if($lazy_load_js){
                        $this->w3StrReplaceSet($final_merge_has_js[$ii],'<script type="lazyload_int" data-src="'.$cache_js_url.'"></script>');
                    }else{
                        $this->w3StrReplaceSet($final_merge_has_js[$ii],'<script defer="defer" src="'.$cache_js_url.'"></script>');
                    }
                }else{
                    $this->w3StrReplaceSet($final_merge_has_js[$ii],'');
                }
            }
        }
    }
    
    function w3CreateJsCombinedCacheFile($final_merge_js, $enable_cdn){
        $file_name = is_array($final_merge_js) ? $this->addSettings['w3_rand_key'].'-'.implode('-', $final_merge_js) : '';
        if(!empty($file_name)){
            $js_file_name = md5($file_name).$this->addSettings['js_ext'];
            if(!is_file($this->w3GetCachePath('all-js').'/'.$js_file_name)){
                $all_js = '';
                foreach($final_merge_js as $key => $script_path){
                    $all_js .= file_get_contents($this->addSettings['documentRoot'].$script_path).";\n";
                }
                $this->w3CreateFile($this->w3GetCachePath('all-js').'/'.$js_file_name, $all_js);
            }
            $main_js_url = $this->addSettings['cacheUrl'].'/all-js/'.$js_file_name;
            $main_js_url = $enable_cdn ? str_replace($this->addSettings['homeUrl'],$this->addSettings['imageHomeUrl'] ,$main_js_url) : $main_js_url;
            return $main_js_url;
        }
    }
    
	function w3_lazy_load_javascript(){
		$google_fonts_delay_load = !empty($this->settings['google_fonts_delay_load']) ? $this->settings['google_fonts_delay_load']*1000 : 2000;
		$script = 'var google_fonts_delay_load = '.$google_fonts_delay_load.';var w3_upload_path="'.str_replace($this->addSettings['documentRoot'],'',$this->addSettings['uploadPath']).'/"; var w3_webp_path="'.$this->addSettings['webp_path'].'";var w3_lazy_load_js = '.(!empty($this->settings['load_combined_js']) && $this->settings['load_combined_js'] == 'after_page_load' ? 1 : 0).';';
		if(!empty($this->settings['exclude_page_from_load_combined_js']) && $this->w3CheckIfPageExcluded($this->settings['exclude_page_from_load_combined_js'])){
			$script.='var w3_excluded_js=1;';
		}else{
			$script.='var w3_excluded_js=0;';
		}
		return $script.file_get_contents(W3SPEEDSTER_PATH.'/assets/js/script-load.min.js');
	}
	function w3_lazy_load_images(){
		$enable_cdn = 0;
		if($this->addSettings['imageHomeUrl'] != $this->addSettings['homeUrl'] ){
			$enable_cdn = 1;
		}
		$exclude_cdn_arr = !empty($this->addSettings['exclude_cdn']) ? $this->addSettings['exclude_cdn'] : array();
		$lazy_load_by_px = !empty($this->settings['lazy_load_px']) ? (int)$this->settings['lazy_load_px'] : 200;
		$script = 'var w3_lazy_load_by_px='.$lazy_load_by_px.';var blank_image_webp_url = "'. (($enable_cdn && !in_array('.png',$exclude_cdn_arr)) ? str_replace($this->addSettings['homeUrl'],$this->addSettings['imageHomeUrl'],$this->addSettings['upload_base_url']): $this->addSettings['upload_base_url']).'/cache/blank.pngw3.webp";';
		$inner_script_optimizer = $script.'function w3_to_webp(t){for(var e=0;e<t.length;e++){if(null!=t[e].getAttribute("data-src")&&""!=t[e].getAttribute("data-src")){var a=t[e].getAttribute("data-src");t[e].setAttribute("data-src",a.replace("w3.webp","").replace(w3_webp_path,w3_upload_path))}if(null!=t[e].getAttribute("data-srcset")&&""!=t[e].getAttribute("data-srcset")){var o=t[e].getAttribute("data-srcset");t[e].setAttribute("data-srcset",o.replace(/w3.webp/g,"").split(w3_webp_path).join(w3_upload_path))}if(null!=t[e].src&&""!=t[e].src){var r=t[e].src;t[e].src=r.replace("w3.webp","").replace(w3_webp_path,w3_upload_path)}if(null!=t[e].srcset&&""!=t[e].srcset){var l=t[e].srcset;t[e].srcset=l.replace(/w3.webp/g,"").split(w3_webp_path).join(w3_upload_path)}}}function fixwebp(){if(!w3_hasWebP){w3_to_webp(document.querySelectorAll("img[data-src$=\'w3.webp\']")),w3_to_webp(document.querySelectorAll("img[src$=\'w3.webp\']")),["*"].forEach((function(t){for(var e=document.getElementsByTagName(t),a=e.length,o=0;o<a;o++){var r=e[o],l=(r.currentStyle||window.getComputedStyle(r,!1)).backgroundImage;l.match("w3.webp")&&(document.all?r.style.setAttribute("cssText",";background-image: "+l.replace("w3.webp","").replace(w3_webp_path,w3_upload_path)+" !important;"):r.setAttribute("style",r.getAttribute("style")+";background-image: "+l.replace("w3.webp","").replace(w3_webp_path,w3_upload_path)+" !important;"))}}))}}function w3_change_webp(){if(bg.match("w3.webp")){document.all?(tag.style.setAttribute("cssText","background-image: "+bg.replace("w3.webp","").replace(w3_webp_path,w3_upload_path)+" !important"),tag.currentStyle||window.getComputedStyle(tag,!1)):(tag.setAttribute("style","background-image: "+bg.replace("w3.webp","").replace(w3_webp_path,w3_upload_path)+" !important"),tag.currentStyle||window.getComputedStyle(tag,!1))}}var w3_hasWebP=!1,w3_bglazyload=1;function w3_events_on_end_js(){document.getElementById("w3_bg_load").remove(),w3_bglazyload=0,lazyloadimages(0)}function w3_start_img_load(){var t=this.scrollY;lazyloadimages(t),lazyloadiframes(t)}function w3_events_on_start_js(){convert_to_video_tag(document.getElementsByTagName("videolazy")),w3_start_img_load()}!function(){var t=new Image;t.onload=function(){w3_hasWebP=!!(t.height>0&&t.width>0)},t.onerror=function(){w3_hasWebP=!1,fixwebp()},t.src=blank_image_webp_url}(),window.addEventListener("scroll",(function(t){w3_start_img_load()}),{passive:!0});var win_width=screen.availWidth,bodyRectMain={};function getDataUrl(t,e,a){var o=document.createElement("canvas"),r=o.getContext("2d"),l=new Image;o.width=parseInt(e),o.height=parseInt(a),r.drawImage(l,0,0),t.src=o.toDataURL("image/png")}function lazyload_img(t,e,a,o){for(var r=0;r<t.length;r++)if("LazyLoad"==t[r].getAttribute("data-class")){var l=t[r],i=t[r].getBoundingClientRect();if(0!=i.top&&i.top-(a-e.top)<w3_lazy_load_by_px){if(compStyles=window.getComputedStyle(t[r]),0==compStyles.getPropertyValue("opacity"))continue;if("IFRAMELAZY"==l.tagName){var n;l=document.createElement("iframe");for(n=t[r].attributes.length-1;n>=0;--n)l.attributes.setNamedItem(t[r].attributes[n].cloneNode());t[r].parentNode.replaceChild(l,t[r])}var d=l.getAttribute("data-src")?l.getAttribute("data-src"):l.src,s=l.getAttribute("data-srcset")?l.getAttribute("data-srcset"):"";s||(l.onload=function(){this.setAttribute("data-done","Loaded"),"function"==typeof w3speedup_after_iframe_img_load&&w3speedup_after_iframe_img_load(this)}),l.src=d,null!=s&""!=s&&(l.srcset=s),delete l.dataset.class}}}function w3_load_dynamic_blank_img(t){for(var e=0;e<t.length;e++)if("LazyLoad"==t[e].getAttribute("data-class")){var a=t[e].src;if(void 0!==a&&-1==a.indexOf("data:")&&null!=t[e].getAttribute("width")&&null!=t[e].getAttribute("height")){var o=parseInt(t[e].getAttribute("width")),r=parseInt(t[e].getAttribute("height"));getDataUrl(t[e],o,r)}}}function convert_to_video_tag(t){const e=t.length>0?t[0]:"";if(e){delete t[0];var a,o=document.createElement("video");for(a=e.attributes.length-1;a>=0;--a)o.attributes.setNamedItem(e.attributes[a].cloneNode());o.innerHTML=e.innerHTML,e.parentNode.replaceChild(o,e),"string"==typeof o.getAttribute("data-poster")&&o.setAttribute("poster",o.getAttribute("data-poster")),convert_to_video_tag(t)}}function lazyload_video(t,e,a,o,r){for(var l=0;l<t.length;l++){t[l];var i=t[l].getBoundingClientRect();if(0!=i.top&&i.top-(o-e.top)<w3_lazy_load_by_px)if(void 0===t[l].getElementsByTagName("source")[0])lazyload_video_source(t[l],a,o,r,i,e);else for(var n=t[l].getElementsByTagName("source"),d=0;d<n.length;d++){lazyload_video_source(n[d],a,o,r,i,e)}}}function lazyload_video_source(t,e,a,o,r,l){if(void 0!==t&&"LazyLoad"==t.getAttribute("data-class")&&0!=r.top&&r.top-(a-l.top)<w3_lazy_load_by_px){var i=t.getAttribute("data-src")?t.getAttribute("data-src"):t.src,n=t.getAttribute("data-srcset")?t.getAttribute("data-srcset"):"";null!=t.srcset&""!=t.srcset&&(t.srcset=n),void 0===t.getElementsByTagName("source")[0]?"SOURCE"==t.tagName?(t.parentNode.src=i,t.parentNode.load(),null!==t.parentNode.getAttribute("autoplay")&&t.parentNode.play()):(t.src=i,t.load(),null!==t.getAttribute("autoplay")&&t.play()):t.parentNode.src=i,delete t.dataset.class,t.setAttribute("data-done","Loaded")}}function lazyload_imgbgs(t,e,a,o){for(var r=0;r<t.length;r++){var l=t[r],i=t[r].getBoundingClientRect();i.top,e.top;i.top-(a-e.top)<w3_lazy_load_by_px&&l.classList.add("w3_bg")}}function lazyloadimages(t){var e=document.querySelectorAll("img[data-class=LazyLoad]"),a=document.querySelectorAll("div:not(.w3_js), section:not(.w3_js), iframelazy:not(.w3_js)"),o=document.getElementsByTagName("video"),r=document.getElementsByTagName("audio"),l=document.body.getBoundingClientRect(),i=window.innerHeight,n=screen.availWidth;"undefined"!=typeof load_dynamic_img&&(w3_load_dynamic_blank_img(e),delete load_dynamic_img),w3_bglazyload&&(l.top<50&&1==bodyRectMain.top||Math.abs(bodyRectMain.top)-Math.abs(l.top)<-50||Math.abs(bodyRectMain.top)-Math.abs(l.top)>50)&&(bodyRectMain=l,lazyload_imgbgs(a,l,i,n)),lazyload_img(e,l,i,n),lazyload_video(o,l,t,i,n),lazyload_video(r,l,t,i,n)}function lazyloadiframes(t){var e=document.body.getBoundingClientRect(),a=window.innerHeight,o=screen.availWidth;lazyload_img(document.querySelectorAll("iframelazy[data-class=LazyLoad]"),e,a,o)}bodyRectMain.top=1,setInterval((function(){lazyloadiframes(top)}),8e3),setInterval((function(){lazyloadimages(0),fixwebp()}),3e3),document.addEventListener("click",(function(){lazyloadimages(0)})),lazyloadimages(0);';
		$custom_js_path = $this->w3GetCachePath('all-js').'/wnw-custom-inner-js.js';
		if(!is_file($custom_js_path)){
			$this->w3CreateFile($custom_js_path,$this->w3CompressJs($inner_script_optimizer));
		}
		return file_get_contents($custom_js_path);
	
	}
	function w3CreateFileCacheJs_url($path){
        $cache_file_path = $this->w3GetCachePath('js').'/'.md5($this->addSettings['w3_rand_key'].$path).'.js';
        if( !file_exists($cache_file_path) ){            
            $html = file_get_contents($path);
            $html = $this->w3_modify_file_cache_js($html, $path);
            $this->w3CreateFile($cache_file_path, $html );
        }
        return str_replace($this->addSettings['documentRoot'],'',$cache_file_path);
    }
    function w3_modify_file_cache_js($html, $path){
        $src_array = explode('/',$path);
        $count = count($src_array);
        unset($src_array[$count-1]);
        if(!empty($this->settings[base64_decode('aXNfYWN0aXZhdGVk')]) && !empty($this->settings['load_combined_js'])){
            if((strpos($html,'holdready:') !== false || strpos($html,'S.holdReady') !== false) && empty($this->addSettings['holdready'])){
                $html .= ';if(typeof($) == "undefined"){$ = jQuery;}else{var $ = jQuery;}';
                $this->addSettings['holdready'] = 1;
            }
            $exclude_from_w3_changes = 0;
            if(function_exists('W3speedster_exclude_internal_js_w3_changes')){
                $exclude_from_w3_changes = W3speedster_exclude_internal_js_w3_changes($path,$html);
            }
            if(strpos($html,'holdready:') === false && !$exclude_from_w3_changes){				
                $html = $this->w3_changes_in_js($html);				
            }
            if( !empty($this->addSettings['jquery_excluded']) && empty($this->addSettings['holdready'])){
                $html = (function_exists('W3speedster_load_2_jquery') ? file_get_contents($this->addSettings['jquery_excluded']) : '').';;if(typeof($) != "undefined"){$ = jQuery;}else{var $ = jQuery;}'.$html;
                $this->addSettings['holdready'] = 1;
            }		
        }
        return $html;
    }
	
}