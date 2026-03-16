<?php
namespace Perspective\Voting\Block\Customer\Link;

use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\DefaultPathInterface;
use Perspective\Voting\Service\ConfigData;

class Votings extends Current
{
    /**
     * @var ConfigData
     */
    protected $configData;

    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param ConfigData $configData
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        ConfigData $configData,
        array $data = []
    ) {
        $this->configData = $configData;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Hide "My Votings" link of customer account navigation if module disabled (configuration)
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->configData->isModuleEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
