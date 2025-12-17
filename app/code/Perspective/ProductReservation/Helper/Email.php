<?php
namespace Perspective\ProductReservation\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

class Email extends AbstractHelper
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var State
     */
    protected $state;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        State $state,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param string $template_id
     * @param string $toEmail
     * @param array $vars
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function send(string $template_id, string $toEmail, array $vars = [])
    {
        // if mail to admin -> send to default magento mail
        if ($toEmail === 'admin_mail') {
            $toEmail = $this->scopeConfig->getValue('trans_email/ident_general/email');
        }
        try {
            $this->state->setAreaCode(Area::AREA_FRONTEND);
        } catch (\Exception $e) {}

        $transport = $this->transportBuilder
            ->setTemplateIdentifier($template_id)
            ->setTemplateOptions([
                'area'  => Area::AREA_FRONTEND,
                'store' => $this->storeManager->getStore()->getId(),
            ])
            ->setTemplateVars($vars)
            ->setFromByScope('general')
            ->addTo($toEmail)
            ->getTransport();

        $transport->sendMessage();
    }
}
