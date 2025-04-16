<?php

declare(strict_types=1);

namespace Lachestry\Configuration\Block\Link;

use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;

class LinkToSeting
{
    protected UrlInterface $urlBuilder;

    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    public function buildLink(?string $path): string|Phrase
    {
        if (!$path) {
            return __('No Path');
        }

        list($section, $group) = explode('/', $path);
        $urlPath = "adminhtml/system_config/edit/section/$section/group/$group";

        return '<a href="' . $this->urlBuilder->getUrl($urlPath) . '">' . $path . '</a>';
    }
}
