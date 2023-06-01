<?php
/**
 * OneStepCheckout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   onestepcheckout
 * @package    onestepcheckout_iosc
 * @copyright  Copyright (c) 2017 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */
declare(strict_types=1);

namespace Onestepcheckout\Iosc\Model\Recaptcha;

/**
 * Adapter for the recaptcha libraries
 * to solve BC breaking between magento2.3 and magento2.4 series
 * while still keep settings for customers convenience
 */
class Adapter implements \Onestepcheckout\Iosc\Model\Recaptcha\AdapterInterface
{
    /**
     *
     * @param \Google\Recaptcha $reCaptcha
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \ReCaptcha\ReCaptchaFactory $reCaptchaFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->reCaptchaFactory = $reCaptchaFactory;
    }

    /**
     *
     * @param string $token
     * @param string $clientIp
     * @param string $hostName
     * @return boolean
     */
    public function validate($token = null, $clientIp = null, $hostName = null)
    {
        if (class_exists(\Magento\ReCaptchaVersion3Invisible\Model\Frontend\UiConfigProvider::class)) {
            $privKey = $this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/private_key');
            $threshold = (float)$this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/score_threshold');
        }
        if (empty($privKey) && class_exists(\MSP\ReCaptcha\Model\LayoutSettings::class)) {
            $privKey = $this->getConfigValue('msp_securitysuite_recaptcha/general/private_key');
            $threshold  = (float)$this->getConfigValue('msp_securitysuite_recaptcha/general/score_threshold');
        }

        /** @var ReCaptcha $reCaptcha */
        $reCaptcha = $this->reCaptchaFactory->create(['secret' => $privKey]);
        $result = $reCaptcha
                    ->setScoreThreshold($threshold)
                    ->setExpectedHostname($hostName)
                    ->verify($token, $clientIp);

        return $result;
    }

    /**
     * Get frontend settigns for captch
     * @return array
     */
    public function getCaptchaSettings()
    {

        $settings = [];
        if (class_exists(\Magento\ReCaptchaVersion3Invisible\Model\Frontend\UiConfigProvider::class)) {

            $settings = [
                'rendering' => [
                    'sitekey' => $this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/public_key'),
                    'badge' => $this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/position'),
                    'size' => 'invisible',
                    'theme' => $this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/theme'),
                    'hl'=> $this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/lang')
                ],
                'invisible' => true,
            ];

        }
        if (empty($settings) && class_exists(\MSP\ReCaptcha\Model\LayoutSettings::class)) {
            $settings = [
                'rendering' => [
                    'sitekey' => $this->getConfigValue('msp_securitysuite_recaptcha/general/public_key'),
                    'badge' => $this->getConfigValue('msp_securitysuite_recaptcha/frontend/position'),
                    'size' => 'invisible',
                    'theme' => $this->getConfigValue('msp_securitysuite_recaptcha/frontend/theme'),
                    'hl'=> $this->getConfigValue('msp_securitysuite_recaptcha/frontend/lang')
                ],
                'invisible' => true,
            ];
        }

        return $settings;
    }

    /**
     * Check if recaptcha has configuration
     * @return boolean
     */
    public function isConfigured()
    {

        $result = false;
        if (class_exists(\Magento\ReCaptchaVersion3Invisible\Model\Frontend\ValidationConfigProvider::class)) {
            if (!empty($this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/public_key')) &&
                !empty($this->getConfigValue('recaptcha_frontend/type_recaptcha_v3/private_key'))
            ) {
                $result = true;
            }
        }
        if (class_exists(\MSP\ReCaptcha\Model\Config::class)) {
            if (!empty($this->getConfigValue('msp_securitysuite_recaptcha/general/public_key')) &&
                !empty($this->getConfigValue('msp_securitysuite_recaptcha/general/private_key')) &&
                $this->getConfigValue('msp_securitysuite_recaptcha/general/type') === 'recaptcha_v3'
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     *
     * @param string $path
     * @return string
     */
    protected function getConfigValue($path)
    {
        return trim((string)$this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }
}
