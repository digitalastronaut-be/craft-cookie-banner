<?php

namespace digitalastronaut\craftcookiebanner\exporters;

use craft\base\Element;
use craft\base\ElementExporter;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\ArrayHelper;

class CookieBannerExporter extends ElementExporter {
    public static function displayName(): string {
        return 'Default';
    }

    public function export(ElementQueryInterface $query): mixed {
        $results = [];

        /** @var ElementQuery $query */
        $query->with(['relatedEntries']);

        foreach ($query->each() as $element) {
            /** @var Element $element */
            $results[] = [
                'Title' => $element->title ?? '',
                'Status' => ucfirst($element->status),
                'URL' => $element->getUrl(),
                'RelatedEntries' => ArrayHelper::getColumn(
                    $element->relatedEntries,
                    'title'
                ),
            ];
        }

        return $results;
    }
}