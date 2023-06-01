<?php

namespace Dev\Blog\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        RequestInterface $request,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->request = $request;
    }
    /**
     * This function will be used to get the css/js file.
     *
     * @param string $asset
     * @return string
     */
    public function getAssetUrl($asset)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $assetRepository = $objectManager->get('Magento\Framework\View\Asset\Repository');
        return $assetRepository->createAsset($asset)->getUrl();
    }

    public function getBlogName()
    {
        $param1 = $this->request->getParam('param1', null);
        $param2 = $this->request->getParam('param2', null);

        return [$param1, $param2];
    }

    public function getBlog($blogName)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://production.wildcountry.in/wp-json/wp/v2/posts/?slug=" . $blogName . "");
        $blog = curl_exec($ch);
        curl_close($ch);
        $blog = json_decode($blog);
        return $blog;
    }

    public function getBlogs($page)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://production.wildcountry.in/wp-json/wp/v2/posts?content_type=3&page=" . $page . "&per_page=5");
        $blogs = curl_exec($ch);
        curl_close($ch);
        $blogs = json_decode($blogs);
        return $blogs;
    }
}