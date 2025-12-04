<?php

namespace digitalastronaut\craftcookiebanner\web\twig;

use Craft;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\records\Content;

class CookieBannerTwigExtension extends AbstractExtension {
    public function getFunctions() {
        return [
            new TwigFunction('renderCookiesListHtml', [$this, 'renderCookiesListHtml']),
        ];
    }

    public function renderCookiesListHtml() {
        $currentSiteId = Craft::$app->getSites()->getCurrentSite()->id;
        $content = Content::find()->where(['siteId' => $currentSiteId])->one();
        $settings = CookieBanner::getInstance()->getSettings();

        $cookiesListHtml = Craft::$app->getView()->renderTemplate('cookie-banner/components/_cookiesList.twig', [
            'settings' => $settings,
            'banner' => $content
        ]);

        echo $cookiesListHtml;
    }
}
