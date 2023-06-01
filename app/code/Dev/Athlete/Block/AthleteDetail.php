<?php

namespace Dev\Athlete\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;

class AthleteDetail extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */

    /**
     * @var RequestInterface
     */
    private $request;

    private $email;
    private $author_id;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        RequestInterface $request,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->email = '';
        $this->author_id = '';
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

    public function getAtheleteDetails()
    {
        $username = $this->request->getParam('username', null);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "production.wildcountry.in/wp-json/wcy/V3/athlete_detail/$username");
        $athelete_details = curl_exec($ch);
        curl_close($ch);
        $athelete_details = json_decode($athelete_details);
        if (isset($athelete_details->data) && $athelete_details->data->status == 404) {
            return false;
        }
        $this->email = $athelete_details->email;
        $this->author_id = $athelete_details->id;
        return $athelete_details;
    }

    public function getAllBlog()
    {
        $username = $this->request->getParam('username', null);

        $author_id = $this->author_id;
        $blog_api_url = "http://production.wildcountry.in/wp-json/wp/v2/posts?author=$author_id&content_format=blog&per_page=4";
        if ($username == 'cocod') {
            $blog_api_url = "http://production.wildcountry.in/wp-json/wp/v2/posts?content_format=blog&per_page=4";
        }        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $blog_api_url);
        $blogs = curl_exec($ch);
        curl_close($ch);
        $blogs = json_decode($blogs);
        return $blogs;
    }

    public function getAllReviews()
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $storemanager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        // $websiteID = $storemanager->getStore()->getWebsiteId();
        // $customerObj = $objectManager->get("\Magento\Customer\Model\CustomerFactory")->create()->setWebsiteId($websiteID)->loadByEmail("anand12@gmail.com");
        // $customerId = $customerObj->getId();
        // $reviewCollection = $objectManager->get("\Magento\Review\Model\ResourceModel\Review\CollectionFactory");

        // $reviewCollection = $reviewCollection->create()
        //     ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
        //     // ->addCustomerFilter($customerId)
        //     ->setDateOrder()->addRateVotes();

        // $data = [];
        // $index = 0;

        // foreach ($reviewCollection as $review) {
        //     $product = $objectManager->create('Magento\Catalog\Model\Product')->load($review->getData('entity_pk_value'));
        //     $attribute = $product->getResource()->getAttribute('image');
        //     $imageUrl = $attribute->getFrontend()->getUrl($product);

        //     $data[$index]['id'] = $product->getData('entity_id');
        //     $data[$index]['name'] = $product->getData('name');
        //     $data[$index]['sku'] = $product->getData('sku');
        //     $data[$index]['type_id'] = $product->getData('type_id');
        //     $data[$index]['brand_name'] = $product->getAttributeText('brand');
        //     $data[$index]['image_url'] = $imageUrl;
        //     $data[$index]['review_id'] = $review->getData('review_id');
        //     $data[$index]['nickname'] = $review->getData('nickname');
        //     $data[$index]['title'] = $review->getData('title');
        //     $data[$index]['detail'] = $review->getData('detail');
        //     $data[$index]['created_at'] = $review->getData('created_at');
        //     $data[$index]['product_url'] = $product->getProductUrl();


        //     $votes = $review->getRatingVotes();
        //     if (count($votes)) {
        //         foreach ($votes as $vote) {
        //             $data[$index]['rating'][$vote->getData('rating_code')] = $vote->getPercent();
        //         }
        //     }
        // }
        // return $data;
        $username = $this->request->getParam('username', null);

        $author_id = $this->author_id;
        $product_review_url = "http://production.wildcountry.in/wp-json/wp/v2/posts?content_format=gear-up-product-review&author=$author_id";
        if ($username == 'cocod') {
            $product_review_url = "http://development.wildcountry.in/wp-json/wp/v2/posts?content_format=gear-up-product-review";
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $product_review_url);
        $reviews = curl_exec($ch);
        curl_close($ch);
        $reviews = json_decode($reviews);
        return $reviews;

    }




}