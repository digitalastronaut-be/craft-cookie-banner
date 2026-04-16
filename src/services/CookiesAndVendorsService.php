<?php

namespace digitalastronaut\craftcookiebanner\services;

use Craft;
use craft\base\Component;

use digitalastronaut\craftcookiebanner\CookieBanner;
use digitalastronaut\craftcookiebanner\helpers\CookieBanner as CookieBannerHelper;
use digitalastronaut\craftcookiebanner\records\Content;

use yii\db\Exception;
use yii\web\BadRequestHttpException;

class CookiesAndVendorsService extends Component {
    /**
     * @param bool $categorized
     * @return array|mixed[]
     */
    public function getAllCookies(bool $categorized = false, ?int $siteId = null) {
        if (!$siteId) $siteId = Craft::$app->sites->getPrimarySite()->id;

        $content = Content::find()->where(['siteId' => $siteId])->one();

        $categorizedCookies = $content->getAttributes(CookieBannerHelper::COOKIE_CATEGORIES);

        if (!$content) return [];

        $uncategorizedCookies = [];

        foreach ($categorizedCookies as $category => $cookies) {
            if (!is_array($cookies)) continue;

            foreach ($cookies as $cookie) {
                $cookie['category'] = $category;
                $uncategorizedCookies[] = $cookie;
            }
        }

        return $categorized ? $categorizedCookies : $uncategorizedCookies;
    }

    /**
     * @param $cookieName
     *
     * @return void
     * @throws Exception
     */
    public function autoCreateCookieForEachSite($cookieName): void {
        $sites = Craft::$app->sites->getAllSites();
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($sites as $site) {
                $data = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getCookieDataFromDatabase($cookieName, explode("-", $site->language)[0]);

                $content = Content::find()->where(['siteId' => $site->id])->one();
    
                if (!$content) {
                    throw new Exception("Content record missing for site {$site->id}");
                }

                $existingCookies = array_column($this->getAllCookies(), null, "name");

                if (in_array($data['cookie']['name'], $existingCookies)) {
                    throw new Exception("This cookie is already defined");
                }

                if ($data['cookie']['category']) {
                    // We need to add a Cookies suffix because the cookies.json doesn't match our db schema
                    $cookiesForCategory = $content->getAttribute($data['cookie']['category'] . 'Cookies');

                    if (!is_array($cookiesForCategory)) $cookiesForCategory = [];

                    $cookiesForCategory[] = [
                        "name" => $data['languageMatch'] ? $data['cookie']['name'] : $cookieName,
                        "vendor" => 'default',
                        "purpose" => $data['languageMatch'] ? $data['cookie']['description'] : null,
                        "expiration" => $data['languageMatch'] ? $data['cookie']['retention'] : null,
                        "enabled" => true,
                    ];

                    $content->setAttribute($data['cookie']['category'] . 'Cookies', $cookiesForCategory);

                    if (!$content->save()) {
                        throw new Exception(sprintf(
                            'Failed saving content for site "%s": %s',
                            $site->name,
                            json_encode($content->getErrors())
                        ));
                    }
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param array  $cookieForEachSite
     * @param string $category
     *
     * @return void
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function createCookieForEachSite (array $cookieForEachSite, string $category): void {
        $sites = Craft::$app->getSites()->getAllSites();
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($sites as $site) {
                $cookie = $cookieForEachSite[$site->id];

                if (!$cookie['name']) {
                    throw new BadRequestHttpException("Cookie name attribute missing for " . $site->name);
                }
                    
                if (array_column($this->getAllCookies(), null, "name")[$cookie['name']]) {
                    throw new Exception("Cookie name {$cookie['name']} Already exists");
                };
                
                $content = Content::find()->where(['siteId' => $site->id])->one();

                if (!$content) {
                    throw new Exception("Content record missing for site {$site->id}");
                }

                $cookies = $content->getAttribute($category) ?? [];

                $cookies[] = [
                    'name' => $cookie['name'],
                    'vendor' => $cookie['vendor'],
                    'purpose' => $cookie['purpose'],
                    'expiration' => $cookie['expiration'],
                    'enabled' => $cookie['enabled'],
                ];

                $content->setAttribute($category, $cookies);

                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed saving content for site "%s": %s',
                        $site->name,
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param string|null $autoFillCookieName
     *
     * @return array
     */
    public function getCreateCookieTableFieldData(string|null $autoFillCookieName = '') {
        $sites = Craft::$app->getSites()->getAllSites();

        $cookieForEachSite = [];

        foreach ($sites as $site) {
            if ($autoFillCookieName) {
                $data = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getCookieDataFromDatabase($autoFillCookieName, explode("-", $site->language)[0]);

                $cookieForEachSite[$site->id] = [
                    'siteName' => $site->name,
                    'name' => $data['cookie']['name'],
                    'vendor' => $data['cookie']['vendor'],
                    'purpose' => $data['languageMatch'] ? $data['cookie']['description'] : "",
                    'expiration' => $data['languageMatch'] ? $data['cookie']['retention'] : "",
                    'enabled' => true,
                ];
            } else {
                $cookieForEachSite[$site->id] = [
                    'siteName' => $site->name,
                    'name' => "",
                    'vendor' => null,
                    'purpose' => "",
                    'expiration' => "",
                    'enabled' => true,
                ];
            }
        }

        return $cookieForEachSite;
    }

    /**
     * @param array  $cookieForEachSite
     * @param string $currentCategory
     * @param string $newCategory
     *
     * @return void
     * @throws Exception
     */
    public function editCookieForEachSite(
        array $cookieForEachSite, 
        string $currentCategory, 
        string $newCategory
    ): void {
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach($cookieForEachSite as $cookie) {
                $site = Craft::$app->sites->getSiteByHandle($cookie['siteHandle']);
                $content = Content::find()->where(['siteId' => $site->id])->one();

                if (!$content) {
                    throw new Exception("Content record missing for site {$site->id}");
                }

                if ($currentCategory !== $newCategory) {
                    $currentCategoryCookies = $content->getAttribute($currentCategory) ?? [];

                    $filteredCurrentCategoryCookies = array_values(array_filter(
                        $currentCategoryCookies,
                        fn($currentCategoryCookie) => $currentCategoryCookie['name'] !== $cookie['name']
                    ));

                    $content->setAttribute($currentCategory, $filteredCurrentCategoryCookies);
                }

                $newCategoryCookies = $content->getAttribute($newCategory) ?? [];
                $newCategoryCookiesByName = array_column($newCategoryCookies, null, 'name');

                $newCategoryCookiesByName[$cookie['name']] = [
                    'name' => $cookie['name'],
                    'vendor' => $cookie['vendor'],
                    'purpose' => $cookie['purpose'],
                    'expiration' => $cookie['expiration'],
                    'enabled' => (bool)($cookie['enabled'] ?? false),
                ];

                $content->setAttribute($newCategory, array_values($newCategoryCookiesByName));

                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed saving content for site "%s": %s',
                        $site->name,
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param string|null $cookieName
     *
     * @return array
     */
    public function getEditCookieTableFieldData(string|null $cookieName): array {
        $contentForEachSite = Content::find()->all();
        
        foreach ($contentForEachSite as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $categories = CookieBannerHelper::COOKIE_CATEGORIES;

            foreach ($categories as $category) {
                $cookies = empty($content->$category) ? [] : $content->$category;

                foreach ($cookies as $cookie) {
                    if ($cookie['name'] !== $cookieName) continue;

                    $cookieForEachSite[] = [
                        'siteName' => $site->name,
                        'name' => $cookie['name'] ?? null,
                        'vendor' => $cookie['vendor'] ?? null,
                        'purpose' => $cookie['purpose'] ?? null,
                        'expiration' => $cookie['expiration'] ?? null,
                        'enabled' => $cookie['enabled'] ?? true,
                        'category' => $category ?? null,
                        'hiddenInputs' => [
                            'siteHandle' => $site->handle,
                        ],
                    ];
                }
            }
        }

        return $cookieForEachSite;
    }

    /**
     * @param string|null $cookieName
     *
     * @return void
     * @throws Exception
     */
    public function deleteCookieForEachSite(string|null $cookieName) {
        $contentForEachSite = Content::find()->all();
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($contentForEachSite as $content) {            
                $categories = CookieBannerHelper::COOKIE_CATEGORIES;
                
                foreach ($categories as $category) {
                    $cookies = $content->$category ?? [];
    
                    if (!is_array($cookies)) continue;
                    
                    $filteredCookies = array_values(array_filter(
                        $cookies,
                        fn($cookie) => ($cookie['name'] ?? null) !== $cookieName
                    ));
    
                    $content->setAttribute($category, $filteredCookies);
                }
    
                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed to save content: %s',
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param string|null $cookieName
     *
     * @return void
     */
    public function blacklistCookieForEachSite(string|null $cookieName): void {
        $settings = CookieBanner::getInstance()->getSettings();

        if (!in_array($cookieName, $settings->blacklistedCookies)) {
            $settings->blacklistedCookies[] = ['name' => $cookieName];
        }

        Craft::$app->plugins->savePluginSettings(
            CookieBanner::getInstance(),
            $settings->toArray()
        );
    }

    public function checkCookieDefinitionForEachSite(string|null $cookieName): array {
        $sites = Craft::$app->getSites()->getAllSites();

        foreach ($sites as $site) {
            $siteKey = $site->name . ' (' . $site->language . ')';

            $content = Content::find()->where(['siteId' => $site->id])->one();

            if (!$content) {
                $result[$siteKey] = 'not-defined';
                continue;
            }

            $matchingCookie = null;
            $existingCookies = array_column($this->getAllCookies(false, $site->id), null, 'name');

            if (isset($existingCookies[$cookieName])) {
                $matchingCookie = $existingCookies[$cookieName];
            }

            if ($matchingCookie === null) {
                $result[$siteKey] = "not-defined";
            } elseif (!$matchingCookie['enabled']) {
                $result[$siteKey] = "disabled";
            } else {
                $hasPurpose = !empty($matchingCookie['purpose']);
                $hasExpiration = !empty($matchingCookie['expiration']);
                
                if ($hasPurpose && $hasExpiration) $result[$siteKey] = "defined";
                else $result[$siteKey] = "defined-incomplete";
            }
        }

        // Sort issues to the beginning of the array so they are not hidden when the bullets are collapsed
        uasort($result, fn($a, $b) => ($b === 'defined-incomplete') <=> ($a === 'defined-incomplete'));

        return $result;
    }

    /**
     * @return array
     */
    public function getCookieChartData(): array {
        $cookies = CookieBanner::getInstance()->getCookieDetection()->getCookiesOverview();

        $metrics = [
            'Defined' => 0,
            'Defined incomplete' => 0,
            'Suggested' => 0,
            'Control panel' => 0,
        ];

        $data = [];

        foreach ($cookies as $cookie) {
            if ($cookie['isControlPanelCookie']) {
                $metrics["Control panel"]++;
                $data[] = [
                    'label' => $cookie['name'],
                    'data' => 'Control panel',
                    'backgroundColor' => '#d8e2ee',
                    ];
                    
                    continue;
                    }
                    
            if ($cookie['onlyInBrowserCookies']) {
                $metrics["Suggested"]++;
                $data[] = [
                    'label' => $cookie['name'],
                    'data' => 'Suggested',
                    'backgroundColor' => '#4299E1',
                    ];
                    
                continue;
            }
                    
            $result = CookieBanner::getInstance()->getCookiesAndVendors()->checkCookieDefinitionForEachSite($cookie['name']);
            
            if (in_array('defined-incomplete', $result, true)) $metrics['Defined incomplete']++;
            else $metrics['Defined']++;
            
            $data[] = [
                'label' => $cookie['name'],
                'data' => in_array('defined-incomplete', $result, true) ? "Defined incomplete" : "Defined",
                'backgroundColor' => in_array('defined-incomplete', $result, true) ? "#facc15" : "#10b981",
            ];
        }

        return [
            'data' => $data,
            'metrics' => $metrics,
        ];
    }

    /**
     * @param string|null $vendorName
     *
     * @return void
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function autoCreateVendorForEachSite(string|null $vendorName): void {
        $sites = Craft::$app->sites->getAllSites();
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($sites as $site) {
                $data = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getVendorDataFromDatabase($vendorName, explode("-", $site->language)[0]);
            
                $content = Content::find()->where(['siteId' => $site->id])->one();

                if (!$content) {
                    throw new Exception("Content record missing for site {$site->id}");
                }

                $vendors = $content->getAttribute('vendors');

                $vendors[] = [
                    'name' => $data['languageMatch'] ? $data['vendor']['name'] : $vendorName, 
                    'url' => $data['vendor']['homePage'], 
                    'description' => $data['languageMatch'] ? $data['vendor']['description'] : null, 
                    'enabled' => true, 
                ];

                $content->setAttribute('vendors', $vendors);
                
                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed saving content for site "%s": %s',
                        $site->name,
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param array $vendorForEachSite
     *
     * @return void
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function createVendorForEachSite(array $vendorForEachSite): void {
        $sites = Craft::$app->getSites()->getAllSites();
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($sites as $site) {
                $vendor = $vendorForEachSite[$site->id];

                if (!$vendor['name']) {
                    throw new BadRequestHttpException("Vendor name attribute missing for " . $site->name);
                }

                $content = Content::find()->where(['siteId' => $site->id])->one();

                if (!$content) {
                    throw new Exception("Content record missing for site {$site->id}");
                }

                $vendors = $content->getAttribute("vendors") ?? [];

                $vendors[] = [
                    'name' => $vendor['name'],
                    'url' => $vendor['url'],
                    'description' => $vendor['description'],
                    'enabled' => $vendor['enabled'],
                ];

                $content->setAttribute("vendors", $vendors);

                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed saving content for site "%s": %s',
                        $site->name,
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param string|null $autoFillVendorName
     *
     * @return array
     */
    public function getCreateVendorTableFieldData(string|null $autoFillVendorName = ""): array {
        $sites = Craft::$app->getSites()->getAllSites();

        $vendorForEachSite = [];

        foreach ($sites as $site) {
            if ($autoFillVendorName) {
                $data = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getVendorDataFromDatabase($autoFillVendorName, explode("-", $site->language)[0]);

                $vendorForEachSite[$site->id] = [
                    'siteName' => $site->name,
                    'name' => $data['languageMatch'] ? $data['vendor']['name'] : $autoFillVendorName,
                    'url' => $data['languageMatch'] ? $data['vendor']['homePage'] : "",
                    'description' => $data['languageMatch'] ? $data['vendor']['description'] : "",
                    'enabled' => true,
                ];
            } else {
                $vendorForEachSite[$site->id] = [
                    'siteName' => $site->name,
                    'name' => "",
                    'url' => "",
                    'description' => "",
                    'enabled' => true,
                ];
            }
        }

        return $vendorForEachSite;
    }

    /**
     * @param array $vendorForEachSite
     *
     * @return void
     * @throws Exception
     */
    public function editVendorForEachSite(array $vendorForEachSite): void {
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($vendorForEachSite as $vendor) {
                $site = Craft::$app->sites->getSiteByHandle($vendor['siteHandle']);
                $content = Content::find()->where(['siteId' => $site->id])->one();
    
                if (!$content) {
                    throw new Exception("Content record missing for site {$site->id}");
                }
    
                $vendors = $content->getAttribute('vendors');
                $vendorsByName = array_column($vendors, null, 'name');
    
                $vendorsByName[$vendor['name']] = [
                    'name' => $vendor['name'], 
                    'url' => $vendor['url'], 
                    'description' => $vendor['description'], 
                    'enabled' => $vendor['enabled'], 
                ];
    
                $content->setAttribute('vendors', array_values($vendorsByName));
    
                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed saving content for site "%s": %s',
                        $site->name,
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param string|null $vendorName
     *
     * @return array
     */
    public function getEditVendorTableFieldData(string|null $vendorName): array {
        $contentForEachSite = Content::find()->all();

        $vendorForEachSite = [];
    
        foreach ($contentForEachSite as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $vendors = $content->getAttribute("vendors");

            foreach ($vendors as $vendor) {
                if ($vendor['name'] !== $vendorName) continue;

                $vendorForEachSite[] = [
                    'siteName' => $site->name,
                    'name' => $vendor['name'],
                    'url' => $vendor['url'],
                    'description' => $vendor['description'],
                    'enabled' => $vendor['enabled'],
                    'hiddenInputs' => [
                        'siteHandle' => $site->handle,
                    ],
                ];
            }
        }

        return $vendorForEachSite;
    }

    /**
     * @param string|null $vendorName
     *
     * @return void
     * @throws Exception
     */
    public function deleteVendorForEachSite(string|null $vendorName): void {
        $contentForEachSite = Content::find()->all();
        $database = Craft::$app->getDb();
        $transaction = $database->beginTransaction();

        try {
            foreach ($contentForEachSite as $content) {
                $filteredVendors = array_values(array_filter(
                    $content['vendors'],
                    fn($vendor) => ($vendor['name'] ?? null) !== $vendorName
                ));
    
                $content->setAttribute('vendors', $filteredVendors);
    
                if (!$content->save()) {
                    throw new Exception(sprintf(
                        'Failed to save content: %s',
                        json_encode($content->getErrors())
                    ));
                }
            }

            $transaction->commit();
        } catch (Exception $error) {
            $transaction->rollBack();
            throw $error;
        }
    }

    /**
     * @param string|null $vendorName
     *
     * @return void
     * @throws Exception
     */
    public function blacklistVendorForEachSite(string|null $vendorName): void {
        $settings = CookieBanner::getInstance()->getSettings();

        if (in_array($vendorName, array_column($settings->blacklistedVendors, null, 'name'))) {
            throw new Exception("Vendor is already blacklisted");
        };

        $settings->blacklistedVendors[] = ['name' => $vendorName];

        Craft::$app->plugins->savePluginSettings(
            CookieBanner::getInstance(),
            $settings->toArray()
        );
    }

    /**
     * @param string|null $vendorName
     * 
     * @return array
     */
    public function checkVendorDefinitionForEachSite(string|null $vendorName): array {
        $cookieBannerContentAllLanguages = Content::find()->all();

        $result = [];

        foreach ($cookieBannerContentAllLanguages as $content) {
            $site = Craft::$app->getSites()->getSiteById($content->siteId);
            $siteKey = $site->name . ' (' . $site->language . ')';

            $vendors = $content['vendors'];

            $matchedVendor = null;
            foreach ($vendors as $vendor) {
                if (isset($vendor['name']) && $vendor['name'] === $vendorName) {
                    $matchedVendor = $vendor;
                    break;
                }
            }

            if ($matchedVendor === null) {
                $result[$siteKey] = "not-defined";
            } elseif (empty($matchedVendor['enabled']) || $matchedVendor['enabled'] === "0") {
                $result[$siteKey] = "disabled";
            } else {
                $hasUrl = !empty($matchedVendor['url']);
                $hasDescription = !empty($matchedVendor['description']);

                if ($hasUrl && $hasDescription) $result[$siteKey] = "defined";
                else $result[$siteKey] = "defined-incomplete";
            }
        }

        // Sort issues to the beginning of the array so they are not hidden when the bullets are collapsed
        uasort($result, fn($a, $b) => ($b === 'defined-incomplete') <=> ($a === 'defined-incomplete'));

        return $result;
    }

    /**
     * @return array
     */
    public function getVendorsChartData(): array {
        $vendors = CookieBanner::getInstance()->getCookieDetection()->getVendorsOverview();

        $metrics = [
            'Defined' => 0,
            'Defined incomplete' => 0,
            'Suggested' => 0,
        ];

        $data = [];

        foreach ($vendors as $vendor) {
            if ($vendor['isSuggestion']) {
                $metrics['Suggested']++;
                $data[] = [
                    'label' => $vendor['name'],
                    'data' => 'Suggested',
                    'backgroundColor' => '#4299E1',
                ];

                continue;
            }

            $result = CookieBanner::getInstance()->getCookiesAndVendors()->checkVendorDefinitionForEachSite($vendor['name']);

            if (in_array('defined-incomplete', $result, true)) $metrics['Defined incomplete']++;
            else $metrics['Defined']++;
            
            $data[] = [
                'label' => $vendor['name'],
                'data' => in_array('defined-incomplete', $result, true) ? "Defined incomplete" : "Defined",
                'backgroundColor' => in_array('defined-incomplete', $result, true) ? "#facc15" : "#10b981",
            ];
        }

        return [
            'data' => $data,
            'metrics' => $metrics,
        ];
    }
}