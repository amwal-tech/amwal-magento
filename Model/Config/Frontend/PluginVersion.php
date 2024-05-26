<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Amwal\Payments\Model\Config;

class PluginVersion extends Field
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * Constructor
     *
     * @param Amwal\Payments\Model\Config $config
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->generateHtml($element, $this->config->getVersion(), $this->config->getGitCommit(), $this->generateCommitLink($this->config->getGitCommit()));
    }

    private function generateCommitLink($commitHash)
    {
        return $commitHash ? 'https://github.com/amwal-tech/amwal-magento/commit/' . $commitHash : '';
    }

    private function generateHtml($element, $version, $commitHash, $commitLink)
    {
        return '
        <div id="' . $element->getHtmlId() . '" style="padding: 10px 0 0 10px; font-family: Arial, sans-serif; font-size: 14px; color: #333;">
            <span style="font-weight: bold;">Version:</span> ' . htmlspecialchars($version, ENT_QUOTES, 'UTF-8') . '<br/>
            <span style="font-weight: bold;">Git Commit:</span>
            <a href="' . htmlspecialchars($commitLink, ENT_QUOTES, 'UTF-8') . '" target="_blank" style="color: #1e88e5; text-decoration: none;">' . htmlspecialchars($commitHash, ENT_QUOTES, 'UTF-8') . '</a>
        </div>';
    }
}
