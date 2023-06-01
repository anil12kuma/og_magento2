<?php
namespace Mguru\Phoneverify\Plugin\Result;

use Magento\Framework\App\ResponseInterface;

class Page
{
    private $context;

    public function __construct(
        \Magento\Framework\View\Element\Context $context
    ) {
        $this->context = $context;
    }

    public function beforeRenderResult(
        \Magento\Framework\View\Result\Page $subject,
        ResponseInterface $response
    ){    
        if($this->context->getRequest()->getFullActionName() == 'ambrand_index_index'){
           $subject->getConfig()->addBodyClass('catalog-category-view');
        }    
        if($this->context->getRequest()->getFullActionName() == 'catalogsearch_result_index'){
           $subject->getConfig()->addBodyClass('catalog-category-view');
        } 
        return [$response];
    }
}