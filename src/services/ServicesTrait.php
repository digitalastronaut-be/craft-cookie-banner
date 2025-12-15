<?php

namespace digitalastronaut\craftcookiebanner\services;

trait ServicesTrait {
    public static function config(): array {
        return [
            'components' => [
                'cookieDetection' => CookieDetectionService::class,
            ],
        ];
    }

    public function getCookieDetection(): CookieDetectionService {
        return $this->get('cookieDetection');
    }
}