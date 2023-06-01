<?php

namespace Meetanshi\IndianGst\Model\Config\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class Options extends AbstractSource
{
    protected $optionFactory;

    public function getAllOptions()
    {
        $this->_options=[
            ['label'=>'Select Rate', 'value'=> '-1'],
            ['label'=>'0%', 'value'=>'0'],
            ['label'=>'0.25%', 'value'=>'0.25'],
            ['label'=>'3%', 'value'=>'3'],
            ['label'=>'5%', 'value'=>'5'],
            ['label'=>'8%', 'value'=>'8'],
            ['label'=>'12%', 'value'=>'12'],
            ['label'=>'18%', 'value'=>'18'],
            ['label'=>'28%', 'value'=>'28']
        ];
        return $this->_options;
    }
}
