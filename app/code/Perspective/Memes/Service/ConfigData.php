<?php
namespace Perspective\Memes\Service;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigData
{
    private const XML_PATH_ENABLED = 'perspective_giphy_memes/general_settings/enabled';
    private const XML_PATH_GIF_COUNT = 'perspective_giphy_memes/general_settings/gif_count';
    private const XML_PATH_API_URL = 'perspective_giphy_memes/general_settings/api_url';
    private const XML_PATH_API_KEY = 'perspective_giphy_memes/general_settings/api_key';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isModuleEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag($this::XML_PATH_ENABLED);
    }

    /**
     * @return int
     */
    public function getGifCount(): int
    {
        return (int)$this->scopeConfig->getValue($this::XML_PATH_GIF_COUNT);
    }

    /**
     * @return string
     */
    public function getGiphyApiUrl(): string
    {
        return $this->scopeConfig->getValue($this::XML_PATH_API_URL);
    }

    /**
     * @return string
     */
    public function getGiphyApiKey(): string
    {
        return $this->scopeConfig->getValue($this::XML_PATH_API_KEY);
    }
}
