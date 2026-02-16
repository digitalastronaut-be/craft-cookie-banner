<?php

namespace digitalastronaut\craftcookiebanner;

use Craft;
use craft\base\Element;
use craft\elements\Entry;
use craft\events\ModelEvent;

use yii\base\Event;

class EventHandlers {
    public static function register(): void {
        self::registerSharedEvents();

        if (Craft::$app->request->isConsoleRequest) self::registerConsoleEvents();
        if (Craft::$app->request->isSiteRequest) self::registerSiteEvents();
        if (Craft::$app->request->isCpRequest) self::registerCpEvents();
    }

    private static function registerSharedEvents(): void {

    }

    private static function registerCpEvents(): void {

    }

    private static function registerSiteEvents(): void {

    }

    private static function registerConsoleEvents(): void {
        
    }
}
