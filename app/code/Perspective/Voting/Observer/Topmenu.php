<?php
namespace Perspective\Voting\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;
use Perspective\Voting\Service\ConfigData;

class Topmenu implements ObserverInterface
{
    protected $configDataService;
    public function __construct(
        ConfigData $configDataService
    ) {
        $this->configDataService = $configDataService;
    }
    public function execute(EventObserver $observer)
    {
        if ($this->configDataService->isShowVotingsLinkInMenu()) {
            $menu = $observer->getMenu();
            $tree = $menu->getTree();
            $data = [
                'name'      => __('Votings'),
                'id'        => 'voting_menu_item',
                'url'       => '/perspective_voting/index/index',
                'is_active' => false
            ];
            $node = new Node($data, 'id', $tree, $menu);
            $menu->addChild($node);
        }
        return $this;
    }
}
