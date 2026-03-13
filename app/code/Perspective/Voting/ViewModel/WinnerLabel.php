<?php
namespace Perspective\Voting\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Perspective\Voting\Service\ActiveWinners;

class WinnerLabel implements ArgumentInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var ActiveWinners
     */
    protected $activeWinnersService;

    /**
     * @param RequestInterface $request
     * @param ActiveWinners $activeWinnersService
     */
    public function __construct(
        RequestInterface $request,
        ActiveWinners $activeWinnersService
    ) {
        $this->request = $request;
        $this->activeWinnersService = $activeWinnersService;
    }

    /**
     * @return string
     */
    public function getWinnerHtml()
    {
        $currentProductId = $this->request->getParam('id');
        return $this->activeWinnersService->getWinnerLabelHtml($currentProductId);
    }
}
