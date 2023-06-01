<?php

namespace Meetanshi\IndianGst\Ui\Component;

use Magento\Ui\Component\ExportButton as CoreExportButton;

class ExportButton extends CoreExportButton
{
    private $removeOption = 'xml';
    /**
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');

        if (isset($config['options'])) {
            $options = [];
            if (isset($config['options'][$this->removeOption])) {
                unset($config['options'][$this->removeOption]);
            }

            foreach ($config['options'] as $option) {
                $option['url'] = $this->urlBuilder->getUrl($option['url']);
                $options[] = $option;
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
