<?php

namespace digitalastronaut\craftcookiebanner\web\twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

class CookieBannerTwigExtension extends AbstractExtension implements GlobalsInterface {
    public function getFunctions() {
        return [
            // new TwigFunction('example', [$this, 'example']),
        ];
    }

    public function getGlobals(): array {
        return [];
    }
}
