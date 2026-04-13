<?php

namespace Perspective\BarberServices\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Perspective\BarberServices\Service\File\Directory as DirectoryService;
use Psr\Log\LoggerInterface;

class CreateDirectoryStructure implements DataPatchInterface
{
    protected $directoryService;
    protected $logger;

    public function __construct(
        DirectoryService $directoryService,
        LoggerInterface $logger
    ) {
        $this->directoryService = $directoryService;
        $this->logger = $logger;
    }

    public function apply()
    {
        $this->logger->info('BarberServices: Running setup patch for directory structure...');
        $this->directoryService->createStructure();

        return $this;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

}
