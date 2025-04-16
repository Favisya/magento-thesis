<?php

declare(strict_types=1);

namespace Lachestry\CronMonitoring\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

/**
 * @method string getGroup()
 * @method object getViewModel()
 */
class CronCard extends Template
{
    protected string $svgLinkTemplate = '';

    public function setLinkSvg(string $template): self
    {
        $this->svgLinkTemplate = $this->getTemplateFile($template);
        return $this;
    }

    public function getLinkSvg(): string
    {
        return $this->fetchView($this->svgLinkTemplate);
    }
}
