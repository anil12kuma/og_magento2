<?php
namespace W3speedster\Optimization\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use W3speedster\Optimization\Model\includes\w3speedsterOptimizeImage;
use W3speedster\Optimization\Helper\Data;
use W3speedster\Optimization\Model\includes\W3speedster;

if (!defined('W3SPEEDSTER_PATH'))
    define('W3SPEEDSTER_PATH', dirname(__DIR__, 3));

class Button extends Field
{
    protected $_template = 'W3speedster_Optimization::system/config/button.phtml';
    protected $_helper;
    public $w3_images;
    protected $dir;
    public function __construct(Data $helper, Context $context, array $data = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
		$this->dir = $directory;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function getCustomUrl()
    {
        return $this->getUrl('router/controller/action');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(['id' => 'start_image_optimization', 'class' => 'start_image_optimization', 'label' => __('Start Webp'),]);
        return $button->toHtml();
    }
    public function getSettings()
    {
        $settings = $this->_helper->getW3Settings();
        $hooks = $this->_helper->getHooksBeforeOpt();
        $mediaPath = $this->dir->getPath('media');
        $img_arr = $this->getDataFile("w3_images", 1);
        if (count($img_arr) == 0) {
			if (!empty($mediaPath)) {
                $this->w3CalcImages($mediaPath);
                $this->updateDataFile("w3_images", $this->w3_images, 1);
                $img_to_opt = count($this->w3_images);
                $img_remaining = count($this->w3_images);
                $this->updateDataFile("w3_images_count", $img_to_opt, 0);
            }
        } else {
			$img_remaining = count($img_arr);
            $img_to_opt = $this->getDataFile("w3_images_count", 0);
        }
        $img_to_opt = empty($img_to_opt) ? 1 : $img_to_opt;
        $opt_offset = (int)$this->getDataFile('w3speedster_opt_offset');
        $data = array('img_to_opt' => $img_to_opt, 'opt_offset' => $opt_offset, 'img_remaining' => $img_remaining);
        return $data;
    }
    public function updateDataFile($filename, $html, $array = 1)
    {        
        $path = $this->dir->getPath('pub') . '/media/cache/' . $filename . '.php';
        $file = fopen($path, 'w');

        fwrite($file, ($array ? json_encode($html) : $html));

        fclose($file);
    }
    public function getDataFile($filename, $is_array = 1)
    {
        if (is_file($this->dir->getPath('pub') . '/media/cache/' . $filename . '.php')) {
            if ($is_array) {
                return (array)json_decode(file_get_contents($this->dir->getPath('pub') . '/media/cache/' . $filename . '.php'));
            } else {
                return file_get_contents($this->dir->getPath('pub') . '/media/cache/' . $filename . '.php');
            }
        }
        if ($is_array) {
            return array();
        } else {
            return '';
        }
    }
    public function w3CalcImages($dir)
    {
        $settings = $this->_helper->getW3Settings();
        $hooks = $this->_helper->getHooksBeforeOpt();
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) != "dir") {
                        if (pathinfo($dir . "/" . $object, PATHINFO_EXTENSION) == "jpeg" || pathinfo($dir . "/" . $object, PATHINFO_EXTENSION) == "jpg" || pathinfo($dir . "/" . $object, PATHINFO_EXTENSION) == "png") {
                            $imgarray = $this->getDataFile("w3_images", 1);
                            if(!empty($this->w3_images) && count($this->w3_images) > 100){
								return;
							}else{
								$this->w3_images[] = $dir . "/" . $object;
							}
                        }
                    } else {
                        $this->w3CalcImages($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
        }
    }
}
