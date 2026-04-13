<?php

namespace Perspective\BarberServices\Service\File;

use Generator;
use Magento\Framework\File\Csv;
use Psr\Log\LoggerInterface;

class CsvReader
{
    protected $csv;
    protected $logger;

    public function __construct(
        Csv $csv,
        LoggerInterface $logger
    ) {
        $this->csv = $csv;
        $this->logger = $logger;
    }

    /**
     * * @param string $path
     * @return Generator
     */
    public function readFile(string $path): Generator
    {
        if (!file_exists($path)) {
            $this->logger->error(sprintf('BarberServices Reader: File not found - %s', $path));
            return;
        }

        $stream = fopen($path, 'r');

        $headers = fgetcsv($stream, 0, ',');

        if (!$headers) {
            fclose($stream);
            return;
        }

        while (($row = fgetcsv($stream, 0, ',')) !== false) {
            if (count($headers) !== count($row)) {
                $this->logger->warning(sprintf('BarberServices Reader: Row length mismatch in %s', $path));
                continue;
            }

            yield array_combine($headers, $row);
        }

        fclose($stream);
    }
}
