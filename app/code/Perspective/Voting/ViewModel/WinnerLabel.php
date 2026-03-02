<?php

namespace Perspective\Voting\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\RequestInterface;
use Perspective\Voting\Service\ActiveWinners;

class WinnerLabel implements ArgumentInterface
{
    protected $request;
    protected $activeWinnersService;
    public function __construct(
        RequestInterface $request,
        ActiveWinners $activeWinnersService
    ) {
        $this->request = $request;
        $this->activeWinnersService = $activeWinnersService;
    }

    public function getWinnerHtml()
    {
        $currentProductId = $this->request->getParam('id');
        return $this->activeWinnersService->getWinnerLabelHtml($currentProductId);
    }
}
