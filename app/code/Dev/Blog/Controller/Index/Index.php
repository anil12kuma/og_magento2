<?php
declare(strict_types=1);

namespace Dev\Blog\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class Index implements HttpGetActionInterface
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    // private $blogName;

    /**
     * @param PageFactory $pageFactory
     * @param RequestInterface $request
     */
    public function __construct(PageFactory $pageFactory, RequestInterface $request)
    {
        $this->pageFactory = $pageFactory;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}