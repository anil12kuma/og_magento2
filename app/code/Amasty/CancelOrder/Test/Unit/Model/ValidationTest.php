<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CancelOrder
 */


namespace Amasty\CancelOrder\Test\Unit\Model;

use Amasty\CancelOrder\Test\Unit\Traits;
use Amasty\CancelOrder\Model\Validation;
use Amasty\CancelOrder\Model\ConfigProvider;

/**
 * @see Validation
 */
class ValidationTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;

    /**
     * @covers Validation::validateOrderAndSettings
     */
    public function testValidateOrderAndSettings()
    {
        $model = $this->getObjectManager()->getObject(
            Validation::class,
            [
                'configProvider' => $this->getConfigProvider(),
                'customerSession' => $this->getCustomerSession()
            ]
        );

        $order = $this->getObjectManager()->getObject(
            \Magento\Sales\Model\Order::class,
            [
                'data' => ['status' => 'processing', 'customer_id' => 1]
            ]
        );

        $this->assertFalse($model->validateOrderAndSettings($order));//is enabled
        $this->assertFalse($model->validateOrderAndSettings($order));// customer group
        $this->assertFalse($model->validateOrderAndSettings($order));// order status
        $this->assertTrue($model->validateOrderAndSettings($order));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getConfigProvider()
    {
        $configProvider = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->setMethods(['isEnabled', 'getEnabledCustomerGroups', 'getEnabledOrderStatuses', 'getRefundType'])
            ->getMock();

        $configProvider->expects($this->any())->method('isEnabled')
            ->willReturnOnConsecutiveCalls(false, true, true, true, true);
        $configProvider->expects($this->any())->method('getEnabledCustomerGroups')
            ->willReturnOnConsecutiveCalls(['1'], ['1', '2'], ['1', '2']);
        $configProvider->expects($this->any())->method('getEnabledOrderStatuses')
            ->willReturnOnConsecutiveCalls(['pending'], ['pending', 'processing']);
        $configProvider->expects($this->any())->method('getRefundType')->willReturn('online');

        return $configProvider;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getCustomerSession()
    {
        $customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerGroupId', 'getCustomerId'])
            ->getMock();

        $customerSession->expects($this->any())->method('getCustomerGroupId')->willReturn('2');
        $customerSession->expects($this->any())->method('getCustomerId')->willReturn(1);

        return $customerSession;
    }
}
