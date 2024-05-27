<?php
declare(strict_types=1);

namespace Amwal\Payments\Model\Config\Frontend;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Amwal\Payments\Model\Config;
use Magento\Framework\Escaper;

class PluginVersion extends Field
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\Backend\Block\Template\Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Config $config,
        \Magento\Backend\Block\Template\Context $context,
        Escaper $escaper,
        array $data = []
    ) {
        $this->config = $config;
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve the HTML markup for the element
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->generateHtml(
            $element,
            $this->config->getVersion(),
            $this->config->getGitCommit(),
            $this->generateCommitLink($this->config->getGitCommit())
        );
    }

    /**
     * Generate the Git commit link
     *
     * @param string|null $commitHash
     * @return string
     */
    private function generateCommitLink(?string $commitHash): string
    {
        return $commitHash ? 'https://github.com/amwal-tech/amwal-magento/commit/' . $commitHash : '';
    }

    /**
     * Generate the HTML for displaying version and commit information
     *
     * @param AbstractElement $element
     * @param string $version
     * @param string|null $commitHash
     * @param string $commitLink
     * @return string
     */
    private function generateHtml(AbstractElement $element, string $version, ?string $commitHash, string $commitLink): string
    {
        return '
        <div id="' . $element->getHtmlId() . '" style="padding: 10px 0 0 10px; font-family: Arial, sans-serif; font-size: 14px; color: #333;">
            <span style="font-weight: bold;">Version:</span> ' . $this->escaper->escapeHtml($version) . '<br/>
            <span style="font-weight: bold;">Git Commit:</span>
            <a href="' . $this->escaper->escapeHtml($commitLink) . '" target="_blank" style="color: #1e88e5; text-decoration: none;">' . $this->escaper->escapeHtml($commitHash) . '</a>
        </div>';
    }
}
