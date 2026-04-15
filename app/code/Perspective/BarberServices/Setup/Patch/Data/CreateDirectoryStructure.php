<?php
namespace Perspective\BarberServices\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Perspective\BarberServices\Service\File\Directory as DirectoryService;
use Psr\Log\LoggerInterface;

class CreateDirectoryStructure implements DataPatchInterface
{
    /**
     * @var DirectoryService
     */
    protected $directoryService;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param DirectoryService $directoryService
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryService $directoryService,
        LoggerInterface $logger
    ) {
        $this->directoryService = $directoryService;
        $this->logger = $logger;
    }

    /**
     * @return $this|CreateDirectoryStructure
     */
    public function apply()
    {
        $this->logger->info('BarberServices: Running setup patch for directory structure...');
        $this->directoryService->createStructure();

        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

}
