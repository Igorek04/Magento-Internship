<?php
namespace Perspective\Voting\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Event\ObserverInterface;
use Perspective\Voting\Service\ConfigData;

class Topmenu implements ObserverInterface
{
    /**
     * @var ConfigData
     */
    protected $configDataService;

    /**
     * @param ConfigData $configDataService
     */
    public function __construct(
        ConfigData $configDataService
    ) {
        $this->configDataService = $configDataService;
    }

    /**
     * Add the Votings link to the top navigation menu if enabled in config
     *
     * @param EventObserver $observer
     * @return $this|void
     */
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
