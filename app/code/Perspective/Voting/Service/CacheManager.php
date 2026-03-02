<?php
namespace Perspective\Voting\Service;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CacheManager
{
    public const VOTING_CACHE_KEY_PREFIX = 'VOTING_DATA_CACHE_%s';
    public const CACHE_TAG = 'PERSPECTIVE_VOTING';
    public const VOTING_WINNERS_CACHE = 'VOTING_WINNERS_CACHE';

    protected $cache;
    protected $configDataService;
    protected $serializer;
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer
    ) {
       $this->cache = $cache;
       $this->serializer = $serializer;
    }

    public function getVotingCache(int $votingId): array
    {
        $cacheId = sprintf(self::VOTING_CACHE_KEY_PREFIX, $votingId);
        $data = $this->cache->load($cacheId);
        if ($data) {
            return $this->serializer->unserialize($data);
        }
        return [];
    }

    public function saveVotingCache(int $votingId, array $data): void
    {
        $cacheId = sprintf(self::VOTING_CACHE_KEY_PREFIX, $votingId);
        $this->cache->save($this->serializer->serialize($data), $cacheId, [self::CACHE_TAG], 3600);
    }

    public function deleteVotingCache(int $votingId): void
    {
        $cacheId = sprintf(self::VOTING_CACHE_KEY_PREFIX, $votingId);
        $this->cache->remove($cacheId);
        //clean by tag?
    }

    public function getWinnersCache(): ?array
    {
        $data = $this->cache->load(self::VOTING_WINNERS_CACHE);
        if ($data) {
            return $this->serializer->unserialize($data);
        } else {
            return null;
        }
    }

    public function saveWinnersCache(array $data): void
    {
        $this->cache->save($this->serializer->serialize($data), self::VOTING_WINNERS_CACHE, [self::CACHE_TAG], 300);
    }

    public function deleteWinnersCache(): void
    {
        $this->cache->remove(self::VOTING_WINNERS_CACHE);
    }





}
