<?php
namespace Perspective\OrderContractFile\Plugin;

class AddContractBlock
{
    /**
     * Insert contract block into order edit form
     */
    public function aroundGetChildHtml(
        \Magento\Sales\Block\Adminhtml\Order\Create\Data $subject,
        \Closure $proceed,
        $alias = '',
        $useCache = true
    ) {
        if ($alias === 'comment') {
            $originalHtml = $proceed($alias, $useCache);

            $customBlockHtml = $subject->getChildHtml('contract_form');

            return $originalHtml . $customBlockHtml;
        }
        return $proceed($alias, $useCache);
    }
}
