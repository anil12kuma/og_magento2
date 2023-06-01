<?php
namespace W3speedster\Optimization\Model\includes;

class w3speedsterOptimizeImage extends W3speedster {
    protected $_curl;
    public $hooks;
    public function __construct($settings,$hooks,$dir,$req){
        $this->hooks = $hooks;
        parent::__construct($settings,$hooks,$dir,$req);
    }		
    function w3speedsterOptimizeSingleImage($metadata, $attachment_id, $context){
        if($this->settings['opt_upload'] != 'on' && ((strtotime("now") - get_post_time('U',false,$attachment_id,''))/60) < 1 ){
            return $metadata;
        }
        if(empty($metadata['file'])){
            $metadata = wp_get_attachment_metadata($attachment_id);
        }		
        //$response = $this->w3_optimize_attachment($this->addSettings['upload_base_url'].'/'.trim($metadata['file'],'/'), $metadata['width'], false);
        $file = explode('/',$metadata['file']);
        array_pop($file);
        $file = implode('/',$file);
        if(!empty($metadata['sizes']['w3speedster-mobile'])){
            $new_thumb_name = str_replace($metadata['sizes']['w3speedster-mobile']['height'],'h',$metadata['sizes']['w3speedster-mobile']['file']);
            rename($this->addSettings['upload_base_dir'].'/'.$file.'/'.$metadata['sizes']['w3speedster-mobile']['file'],$this->addSettings['upload_base_dir'].'/'.$file.'/'.$new_thumb_name);
            $metadata['sizes']['w3speedster-mobile']['file'] = $new_thumb_name;
        }
        if(!empty($metadata['sizes'])){
            $i=0;
            foreach($metadata['sizes'] as $key=>$thumb){				
                //$response = $this->w3_optimize_attachment($this->addSettings['upload_base_url'].'/'.$file.'/'.$thumb['file'], $thumb['width'], true , $this->addSettings['upload_base_url'].'/'.trim($metadata['file'],'/'));
            }			
        }
        return $metadata;
    }
    function w3_optimize_attachment_id($attach_id){
        $metadata = wp_generate_attachment_metadata($attach_id,get_attached_file($attach_id, true));
        if(!empty($metadata)){
            wp_update_attachment_metadata( $attach_id, $metadata );
            return true;
        }else{
            return true;
        }
    }
    function w3_increment_prioritized_img($attach_id){
        $opt_priority = $this->getDataFile('w3speedster_opt_priortize');
        if(empty($opt_priority)){
            $opt_priority = array();
        }
        if(empty($opt_priority) || !in_array($attach_id,$opt_priority)){
            $opt_priority[] = $attach_id;
        }
        $this->updateDataFile('w3speedster_opt_priortize',$opt_priority,'no');
        return true;
    }
    function w3_optimize_attachment_url($path){
        global $wpdb;
        if(strpos($path,'/themes/') !== false){
            return $this->w3_increment_prioritized_img($path);
        }
        $query = "SELECT ID FROM {$wpdb->prefix}posts WHERE post_type='attachment' AND guid like '%".$path."' limit 0,1";
        $attach_id = $wpdb->get_var($query);
        if(!empty($attach_id)){
            return $this->w3_increment_prioritized_img($attach_id);
        }else{
            $path_arr = explode('/',$path);
            $img = array_pop($path_arr);
            $attach_id = $wpdb->get_var("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key='_wp_attachment_metadata' AND meta_value LIKE '%".$img."%'");
            if(!empty($attach_id)){
                return $this->w3_increment_prioritized_img($attach_id);
            }else{
                return $this->w3_increment_prioritized_img($path);
            }
        }
    }
    function w3speedsterOptimizeImageCallback($curl){
        
        $total_count = (int)$this->getDataFile("w3_images_count",0);
        $offset_limit = !empty($this->request->getParam('w3_limit')) && (int)$this->request->getParam('w3_limit') > 0 ? (int)$this->request->getParam('w3_limit') : 2;
        $attach_arr = $this->getDataFile("w3_images",1);
        $images= array();
        if(!empty($attach_arr) && count($attach_arr) > 0){
            $i = 0;
            foreach($attach_arr as $key=>$attach_id){
                $this->w3_optimize_attachment($attach_id,$curl,false);
                $images[] = $attach_id;
                unset($attach_arr[$key]);
                if($i == 1){
                    break;
                }
                $i++;
            }
            $this->updateDataFile('w3_images',$attach_arr,1);
        }
        echo json_encode(array_merge(array('offset'=>$total_count-count($attach_arr)),$images));
        exit;
    }
    function w3_optimize_attachment($image_url_path,$curl,$overwrite=false){
        
        $webp_jpg = !empty($this->settings['webp_jpg']) ? 1 : 0;
        $webp_png = !empty($this->settings['webp_png']) ? 1 : 0;
        $optimize_image = !empty($this->settings['opt_jpg_png']) ? 1 : 0;
        $type = explode('.',$image_url_path);
        $type = array_reverse($type);
        $image_url = str_replace($this->addSettings['upload_base_dir'],$this->addSettings['upload_base_url'],$image_url_path);
        
        $url_array = $this->w3ParseUrl($image_url);
        $image_size = !empty($image_width) ? array($image_width) : @getimagesize($image_url_path);
        $image_type = array('gif','jpg','png','jpeg');
        if( $optimize_image && in_array($type[0],$image_type) && !is_file($image_url_path.'org.'.$type[0]) ){
            if($image_size[0] > 1920){
                $return['img'] = 3;
                return $return;
            }
            $optmize_image = $this->optimize_image($image_size[0],$image_url,$curl,0);
            $optimize_image_size = @imagecreatefromstring($optmize_image);
            if(empty($optimize_image_size)){
                $return['img'] = 2;
            }else{    
                if(!is_file($image_url_path.'org.'.$type[0])){
                    @rename($image_url_path,$image_url_path.'org.'.$type[0]);
                }
                @unlink($image_url_path);
                @file_put_contents($image_url_path,$optmize_image);
                @chmod($image_url_path, 0644);
                $return['img'] = 1;
            }
            
        }else{
            $return['img'] = 0;
        }
        if( ($type[0] == 'png' && $webp_png == 1) || ( in_array($type[0],array('jpg','jpeg')) && $webp_jpg == 1 ) ){
            $webp_path = str_replace($this->addSettings['uploadPath'],$this->addSettings['webp_upload_path'],$image_url_path);
            if(!is_file($webp_path.'w3.webp')){
                $webp_path_arr = explode('/',$webp_path);
                array_pop($webp_path_arr); 
                $this->w3_check_if_folder_exists(implode('/',$webp_path_arr));
                $optmize_image = $this->optimize_image($image_size[0],$image_url,$curl,1);
                file_put_contents($webp_path.'w3.webp',$optmize_image);
                chmod($webp_path.'w3.webp', 0644);
                if(filesize($webp_path.'w3.webp') < 1024){
                    @unlink($webp_path.'w3.webp');
                    $return['webp'] = 0;
                }else{
                    $return['webp']=1;
                }
            }
            
        }
        return $return;
    }

    function w3speedster_resize_image( $file, $dest_path, $max_w) {

        $image = wp_load_image( $file );
        if ( !is_resource( $image ) )
            return new WP_Error( 'error_loading_image', $image, $file );

        $size = @getimagesize( $file );
        if ( !$size )
            return new WP_Error('invalid_image', __('Could not read image size'), $file);
        list($orig_w, $orig_h, $orig_type) = $size;

        $dst_h = $orig_h*$max_w /$orig_w ;
        $dst_w = $max_w;
        $newimage = wp_imagecreatetruecolor( $dst_w, $dst_h );		
        imagecopyresampled( $newimage, $image, 0, 0, 0, 0, $dst_w, $dst_h, $orig_w, $orig_h);

        if ( IMAGETYPE_PNG == $orig_type && function_exists('imageistruecolor') && !imageistruecolor( $image ) )
            imagetruecolortopalette( $newimage, false, imagecolorstotal( $image ) );

        imagedestroy( $image );

        $info = pathinfo($file);
        $dir = $info['dirname'];
        $ext = $info['extension'];
        $name = wp_basename($file, ".$ext");

        if ( !is_null($dest_path) and $_dest_path = realpath($dest_path) )
            $dir = $_dest_path;
        $destfilename = $dest_path;

        if ( IMAGETYPE_GIF == $orig_type ) {
            if ( !imagegif( $newimage, $destfilename ) )
                return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
        } elseif ( IMAGETYPE_PNG == $orig_type ) {
            if ( !imagepng( $newimage, $destfilename ) )
                return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
        } else {
            $destfilename = $dest_path;
            $return = imagejpeg( $newimage, $destfilename, apply_filters( 'jpeg_quality', $jpeg_quality, 'image_resize' ) );
            if ( !$return )
                return new WP_Error('resize_path_invalid', __( 'Resize path invalid' ));
        }
        imagedestroy( $newimage );
        $stat = stat( dirname( $destfilename ));
        $perms = $stat['mode'] & 0000666;
        @chmod( $destfilename, $perms );
        return $destfilename;
    }
}