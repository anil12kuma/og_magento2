<?php
/**
 * Class BrandActions
 *
 * PHP version 7
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Brand\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;

/**
 * Class BrandActions
 *
 * @category Sparsh
 * @package  Sparsh_Brand
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class BrandActions extends Column
{
    /**
     * Constant for Edit Url
     *
     * @var BRAND_EDIT_URL_PATH
    */
    const BRAND_EDIT_URL_PATH = 'brands/index/edit';

    /**
     * Constant for Delete Url
     *
     * @var BRAND_DELETE_URL_PATH
    */
    const BRAND_DELETE_URL_PATH = 'brands/index/delete';

    /**
     * UrlInterface
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Edit url
     *
     * @var self::BRAND_EDIT_URL_PATH
     */
    protected $editUrl;
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * BrandActions constructor.
     *
     * @param ContextInterface    $context            context
     * @param UiComponentFactory  $uiComponentFactory uiComponentFactory
     * @param UrlInterface        $urlBuilder         urlBuilder
     * @param array               $components         components
     * @param array               $data               data
     * @param BRAND_EDIT_URL_PATH $editUrl            editUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $editUrl = self::BRAND_EDIT_URL_PATH
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['brand_id'])) {
                    $title = $this->getEscaper()->escapeHtmlAttr($item['title']);
                    $item[$name]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            $this->editUrl,
                            ['brand_id' => $item['brand_id']]
                        ),
                        'label' => __('Edit')
                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::BRAND_DELETE_URL_PATH,
                            ['brand_id' => $item['brand_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __(
                                'Are you sure you want to delete a %1 record?', $title
                            )
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get instance of escaper
     *
     * @return Escaper
     * @deprecated 101.0.7
     */
    private function getEscaper()
    {
        if (!$this->escaper) {
            $this->escaper = ObjectManager::getInstance()->get(Escaper::class);
        }
        return $this->escaper;
    }
}
