<?php

namespace digitalastronaut\craftcookiebanner\services;

trait ServicesTrait {
    public static function config(): array {
        return [
            'components' => [
                'cookieDetection' => CookieDetectionService::class,
                'consentRecords' => ConsentRecordsService::class,
            ],
        ];
    }

    public function getCookieDetection(): CookieDetectionService {
        return $this->get('cookieDetection');
    }

    public function getConsentRecords(): ConsentRecordsService {
        return $this->get('consentRecords');
    }
}