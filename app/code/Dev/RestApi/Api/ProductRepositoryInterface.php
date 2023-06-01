<?php
namespace Dev\RestApi\Api;

interface ProductRepositoryInterface
{

    /**
     * Return a filtered product.
     *
     * @param string $categoryName
     * @param string $categoryId
     * @param string $subCategoryId
     * @param string $brandId
     * @param mixed $searchCriteria
     * @param string $sortBy
     * @param string $orderDirection
     * @param int $pageSize
     * @param int $currentPage
     * @return \Dev\RestApi\Api\ResponseItemListInterface
     */
    public function getItemLists(string $categoryName = null, $categoryId = '', $subCategoryId = null, $brandId = '', $searchCriteria = [], $sortBy = '', $orderDirection = 'desc', int $pageSize = 10, int $currentPage = 1);

    /**
     * Return a product.
     *
     * @param int $productId
     * @return object[]
     */
    public function getItem($productId);

    /**
     * Return a product link.
     *
     * @param int $productId
     * @param string $linkType
     * @param int $pageSize
     * @param int $currentPage
     * @return object[]
     */
    public function getProductLink($productId, $linkType, int $pageSize = 10, int $currentPage = 1);

    /**
     * Return a product review.
     *
     * @param int $productId
     * @param int $pageSize
     * @param int $currentPage
     * @return object[]
     */
    public function getPublicProductReview($productId, $pageSize = 10, $currentPage = 1);

    /**
     * Return a product review.
     *
     * @param int $productId
     * @param int $pageSize
     * @param int $currentPage
     * @return object[]
     */
    public function getAtheleteProductReview($productId, $pageSize = 10, $currentPage = 1);

    /**
     * Return a product review.
     *
     * @param int $productId
     * @param int $customerId
     * @param string $title
     * @param string $detail
     * @param string $nickname
     * @param \Dev\RestApi\Api\RequestProductReviewInterface $rating
     * @return object[]
     */
    public function addProductReview($productId, $customerId = null, $title = '', $detail, $nickname, $rating);

    /**
     * Return a product stock status.
     *
     * @param int $productId
     * @param string $attributeNameTo
     * @param string $attributeNameFrom
     * @param string $optionId
     * @return object[]
     */
    public function getAvailableAttribute($productId, $attributeNameTo, $attributeNameFrom, $optionId);

    /**
     * 
     * @return \Dev\RestApi\Api\ResponseSliderInterface
     */
    public function getSliders();

    /**
     * @param int $pageSize
     * @param int $currentPage
     * @return \Dev\RestApi\Api\ResponseAthleteInterface
     */
    public function getAthletes(int $pageSize = 10, int $currentPage = 1);

    /**
     * @param int $pageSize
     * @param int $currentPage
     * @return \Dev\RestApi\Api\ResponseAthleteInterface
     */
    public function getBrands(int $pageSize = 10, int $currentPage = 1);

    /**
     * @param string $categoryId
     * @param string $brandId
     * @return object[]
     */
    public function getFilterList($categoryId = '', $brandId = '');

    /**
     * Return a shop by category section.
     *
     * @return object[]
     */
    public function getShopByCategory();

    /**
     * Return a Ads section.
     * 
     * @return object[]
     */
    public function getAds();

    /**
     * Return all categories section.
     *
     * @param int pageSize
     * @param int currentPage
     * @return object[]
     */
    public function getAllCategory(int $pageSize = 10, int $currentPage = 1);

    /**
     * Return a best category deals section.
     *
     * @return object[]
     */
    public function getBestCategoryDeals();

    /**
     * Return all wishlist products.
     *
     * @param int pageSize
     * @param int currentPage
     * @return object[]
     */
    public function getWishlistProducts(int $pageSize = 10, int $currentPage = 1);

    /**
     * Return a msg that product is added to wishlist.
     *
     * @param int $productId
     * @param int $qty
     * @param int $color
     * @param int size
     * @return object[]
     */
    public function saveProductToWishlist($productId, $qty = 1, $color = 0, $size = 0);

    /**
     * Return a msg that product is removed from wishlist.
     *
     * @param int $productId
     * @return object[]
     */
    public function removeProductFromWishlist($productId);

    /**
     * Return a customer Token.
     *
     * @param string $email
     * @return \Dev\RestApi\Api\ResponseSingleObjectInterface
     */
    public function createCustomerToken($email);

    /**
     * Return a social customer details.
     *
     * @param string $email
     * @param string $firstname
     * @param string $lastname
     * @return \Dev\RestApi\Api\ResponseMsgInterface
     */
    public function createSocialCustomer($email, $firstname, $lastname);

    /**
     * Return a filtered product.
     *
     * @return \Dev\RestApi\Api\ResponseItemListInterface
     */
    public function getNewlyProducts();

    /**
     * Return a filtered product.
     *
     * @return \Dev\RestApi\Api\ResponseItemListInterface
     */
    public function getTrendingProducts();

    /**
     * Return a filtered product.
     *
     * @return \Dev\RestApi\Api\ResponseItemListInterface
     */
    public function getDealsProducts();

    /**
     * Return a filtered product.
     *
     * @return \Dev\RestApi\Api\ResponseItemListInterface
     */
    public function getRecentlyViewedProducts();

    /**
     * Return a filtered product.
     *
     * @param int $productId
     * @param int $productPrice
     * @return int $quoteId
     */
    public function buyNow($productId, $productPrice);

    /**
     * Return a filtered product.
     *
     * @param int $quoteId
     * @return object[]
     */
    public function deleteBuyNow($quoteId);

    /**
     * Return a filtered product.
     *
     * @param int $orderId
     * @return object[]
     */
    public function createRazorPayOrder($orderId);

    /**
     * Return a filtered product.
     *
     * @param string $razorpay_payment_id
     * @param string $razorpay_order_id
     * @param string $razorpay_signature
     * @param string $receipt_id
     * @return object[]
     */
    public function validateRazorPaySignature($razorpay_payment_id, $razorpay_order_id, $razorpay_signature, $receipt_id);

    /**
     * Return a filtered product.
     *
     * @param int $brandId
     * @param int pageSize
     * @param int currentPage
     * @return object[]
     */
    public function getReviewByBrand($brandId, $pageSize = 10, $currentPage = 1);

    /**
     * Return a return reason.
     *
     * @return object[]
     */
    public function getReturnOptions();

    /**
     * Return a return resolution.
     *
     * @return object[]
     */
    public function createReturnOrderItem();

    /**
     * Return a return resolution.
     *
     * @param int $id
     * @return object[]
     */
    public function cancelReturnOrderItem($id);

    /**
     * Return a return resolution.
     *
     * @return object[]
     */
    public function getCouponList();

    /**
     * Return a return resolution.
     *
     * @return object[]
     */
    public function getInvoiceList();

    /**
     * Return a return message.
     *
     * @param string $mobile_no
     * @return object[]
     */
    public function sendOtp($mobile_no);

    /**
     * Return a return message.
     *
     * @param string $mobile_no
     * @return object[]
     */
    public function verifyNumber($mobile_no);

    /**
     * Return a return message.
     *
     * @param string $mobile_no
     * @param string $otp
     * @return object[]
     */
    public function verifyOtp($mobile_no, $otp);

    /**
     * Return a return athletes.
     *
     * @return object[]
     */
    public function getAllAthletes();


}