<?php
/**
 * Cookie banner plugin for Craft CMS
 *
 * Provides a fully configurable GDPR-compliant cookie banner for
 * Craft CMS. Supports cookie detection/suggestion, consent records, vendor
 * management, and customizable appearance & content — all from within the
 * Craft control panel.
 *
 * @link      https://digitalastronaut.be
 * @copyright Copyright (c) 2026 Digitalastronaut
 */

namespace digitalastronaut\craftcookiebanner\controllers;

use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;

use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\BadRequestHttpException;
use yii\web\Response;

use digitalastronaut\craftcookiebanner\CookieBanner;

use Fuse\Fuse;

use Throwable;

/**
 * Class CookiesAndVendorsController
 *
 * @author      Digitalastronaut
 * @package     CookieBanner
 * @since       v1.0.0-beta
 */
class CookiesAndVendorsController extends Controller {
    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    /**
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionIndex(): Response {
        $this->requirePermission('cookie-banner:access-cookies-and-vendors');
        
        $settings = CookieBanner::getInstance()->getSettings();

        return $this->renderTemplate("cookie-banner/pages/_cookiesAndVendors", [
            'settings' => $settings,
        ]);
    }

    /**
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @return Response
     */
    public function actionBulkCreate(): Response {
        $this->requirePermission('cookie-banner:add-cookies');

        if ($this->request->isPost) {
            try {
                $input = $this->request->getBodyParam("input");
    
                if (!$input) throw new BadRequestHttpException("Input cannot be empty");
    
                $parsedInput = str_getcsv(trim($input), ",", '"');
    
                foreach ($parsedInput as $item) {
                    $matchedCookie = CookieBanner::getInstance()
                        ->getCookieDetection()
                        ->getCookieDataFromDatabase($item, "en");
    
                    if ($matchedCookie) {
                        CookieBanner::getInstance()
                            ->getCookiesAndVendors()
                            ->autoCreateCookieForEachSite($matchedCookie['cookie']['name']);
                            
                        continue;
                    }
                            
                    $matchedVendor = CookieBanner::getInstance()
                        ->getCookieDetection()
                        ->getVendorDataFromDatabase($item, "en");
                            
                    if ($matchedVendor) {
                        CookieBanner::getInstance()
                            ->getCookiesAndVendors()
                            ->autoCreateVendorForEachSite($matchedVendor['vendor']['name']);
                                
                        continue;
                    }
                }

                Craft::$app->getSession()->setSuccess("Items successfully added");
                $this->redirectToPostedUrl();

            } catch(Throwable $error) {
                Craft::error($error->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

                return $this->redirectToPostedUrl();
            }
        }

        return $this->renderTemplate("cookie-banner/pages/_bulkCreate");
    }

    /**
     * @throws ForbiddenHttpException
     * @throws BadRequestHttpException
     * @return Response
     */
    public function actionPreviewBulkCreate(): mixed {
        $this->requirePermission('cookie-banner:add-cookies');
        
        try {
            $this->requirePostRequest();
            
            $input = $this->request->getBodyParam("input");
            
            if (!$input) throw new BadRequestHttpException("Input cannot be empty");
            
            $parsedInput = str_getcsv(trim($input), ",", '"');

            $cookieDatabase = CookieBanner::getInstance()
                ->getCookieDetection()
                ->getDatabaseFile("cookies", "en");

            $fuzzySearchIndex = new Fuse($cookieDatabase['data'], [
                'keys' => ['name'],
                'threshold' => 0.3,
            ]);

            foreach ($parsedInput as $item) {
                $matchedCookie = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getCookieDataFromDatabase($item, "en");

                if ($matchedCookie) {
                    $results['cookies'][] = [
                        'name' => $matchedCookie['name'] ?? $item,
                        'duplicate' => CookieBanner::getInstance()
                            ->getCookiesAndVendors()
                            ->isDuplicate($matchedCookie['name'] ?? $item)
                    ];

                    continue;
                }

                $matchedVendor = CookieBanner::getInstance()
                    ->getCookieDetection()
                    ->getVendorDataFromDatabase($item, "en");

                if ($matchedVendor) {
                    $results['vendors'][] = [
                        'name' => $matchedVendor['name'] ?? $item,
                        'duplicate' => CookieBanner::getInstance()
                            ->getCookiesAndVendors()
                            ->isDuplicate($matchedVendor['name'] ?? $item)
                    ];

                    continue;
                }

                $suggestionResults = $fuzzySearchIndex->search($item);

                $results['manual'][] = [
                    'name' => $item,
                    'suggestions' => array_slice($suggestionResults, 0, 3),
                    'duplicate' => CookieBanner::getInstance()
                        ->getCookiesAndVendors()
                        ->isDuplicate($item)
                ];
            }
        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);

            return $this->renderTemplate("cookie-banner/requests/_bulkCreatePreview", [
                'error' => $error->getMessage(),
            ]);
        }

        return $this->renderTemplate("cookie-banner/requests/_bulkCreatePreview", [
            'data' => $results,
            'error' => null,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws ForbiddenHttpException
     * @throws MethodNotAllowedHttpException
     */
    public function actionCreateCookie(): Response {
        $this->requirePermission('cookie-banner:add-cookies');

        if ($this->request->isPost) {
            $this->requirePostRequest();

            try {
                $cookieName = $this->request->getBodyParam('name');
                $cookieForEachSite = $this->request->getBodyParam('fields.cookieForEachSite', []);
                $category = $this->request->getBodyParam('category');

                CookieBanner::getInstance()
                    ->getCookiesAndVendors()
                    ->createCookieForEachSite($cookieForEachSite, $category);
                
                Craft::$app->getSession()->setSuccess("{$cookieName} cookie created for all sites.");
                return $this->redirect('cookie-banner/cookies-and-vendors');
                
            } catch(Throwable $error) {
                Craft::error($error->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

                return $this->redirectToPostedUrl();
            }
        }

        $autoFillCookieName = $this->request->getQueryParam('autoFillCookie');

        $settings = CookieBanner::getInstance()->getSettings();
        $cookieForEachSite = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getCreateCookieTableFieldData($autoFillCookieName);

        return $this->renderTemplate("cookie-banner/pages/_createCookie", [
            'settings' => $settings,
            'cookieForEachSite' => $cookieForEachSite,
        ]);
    }

    /**
     * @throws BadRequestHttpException
     * @return Response
     */
    public function actionAutoAddCookie(): Response {
        $this->requirePermission("cookie-banner:add-cookies");
        $this->requirePostRequest();

        try {
            $cookieName = $this->request->getBodyParam('cookieName');

            if (!$cookieName) throw new BadRequestHttpException('Missing body param cookieName');

            CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->autoCreateCookieForEachSite($cookieName);

            Craft::$app->getSession()->setSuccess("{$cookieName} cookie auto created for all sites.");
            return $this->redirect('cookie-banner/cookies-and-vendors');

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }

    /**
     * @param string $cookieName
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MethodNotAllowedHttpException
     * @throws MissingComponentException
     */
    public function actionEditCookie(string $cookieName): Response {
        $this->requirePermission('cookie-banner:edit-cookies');

        if ($this->request->isPost) {
            $this->requirePostRequest();

            try {
                $cookieForEachSite = $this->request->getBodyParam('fields.cookieForEachSite', []);
                $currentCategory = $this->request->getBodyParam('currentCategory');
                $newCategory = $this->request->getBodyParam('category');

                CookieBanner::getInstance()
                    ->getCookiesAndVendors()
                    ->editCookieForEachSite($cookieForEachSite, $currentCategory, $newCategory);

                Craft::$app->getSession()->setSuccess("{$cookieName} cookie saved for all sites.");
                return $this->redirect('cookie-banner/cookies-and-vendors');

            } catch(Throwable $error) {
                Craft::error($error->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

                return $this->redirectToPostedUrl();
            }
        }

        $settings = CookieBanner::getInstance()->getSettings();
        $cookieForEachSite = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getEditCookieTableFieldData($cookieName);

        return $this->renderTemplate("cookie-banner/pages/_editCookie", [
            'settings' => $settings,
            'cookieForEachSite' => $cookieForEachSite,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     */
    public function actionDeleteCookieForAllSites(): Response {
        $this->requirePermission("cookie-banner:remove-cookies");

        try {
            $cookieName = $this->request->getBodyParam("cookieName");

            if (!$cookieName) throw new BadRequestHttpException('Missing body param cookieName');

            CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->deleteCookieForEachSite($cookieName);

            Craft::$app->getSession()->setSuccess("{$cookieName} cookie removed for all sites.");
            return $this->redirect('cookie-banner/cookies-and-vendors');

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     */
    public function actionBlacklistCookie(): Response {
        $this->requirePermission("cookie-banner:blacklist-cookies");

        try {
            $cookieName = $this->request->getParam('cookieName');
            
            if (!$cookieName) throw new BadRequestHttpException('Missing body param cookieName');

            CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->blacklistCookieForEachSite($cookieName);

            Craft::$app->getSession()->setSuccess("{$cookieName} added to blacklist.");
            return $this->redirect('cookie-banner/cookies-and-vendors');

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }

    public function actionGetCookiesChartData(): Response {
        $this->requirePermission('cookie-banner:access-cookies-and-vendors');

        $data = CookieBanner::getInstance()->getCookiesAndVendors()->getCookieChartData();

        return $this->asJson($data);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     */
    public function actionCreateVendor(): Response {
        $this->requirePermission("cookie-banner:add-vendors");

        if ($this->request->isPost) {
            $this->requirePostRequest();

            try {
                $vendorName = $this->request->getBodyParam('name');
                $vendorForEachSite = $this->request->getBodyParam('fields.vendorForEachSite', []);

                CookieBanner::getInstance()
                    ->getCookiesAndVendors()
                    ->createVendorForEachSite($vendorForEachSite);

                Craft::$app->getSession()->setSuccess("{$vendorName} vendor created for all sites.");
                return $this->redirect('cookie-banner/cookies-and-vendors');
            } catch(Throwable $error) {
                Craft::error($error->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

                return $this->redirectToPostedUrl();
            }
        }

        $autoFillVendorName = $this->request->getQueryParam('autoFillVendor');
        $vendorForEachSite = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getCreateVendorTableFieldData($autoFillVendorName);

        return $this->renderTemplate("cookie-banner/pages/_createVendor", [
            'vendorForEachSite' => $vendorForEachSite,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionAutoAddVendor(): Response {
        $this->requirePermission('cookie-banner:add-vendors');
        $this->requirePostRequest();
        
        try {
            $vendorName = $this->request->getBodyParam('vendorName');

            if (!$vendorName) throw new BadRequestHttpException('Missing body param vendorName');

            CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->autoCreateVendorForEachSite($vendorName);

            Craft::$app->getSession()->setSuccess("{$vendorName} vendor auto created for all sites.");
            return $this->redirect('cookie-banner/cookies-and-vendors');

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }

    /**
     * @param string $vendorName
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     */
    public function actionEditVendor(string $vendorName): Response {
        $this->requirePermission('cookie-banner:edit-vendors');        
        
        if ($this->request->isPost) {
            $this->requirePostRequest();

            try {
                $vendorForEachSite = $this->request->getBodyParam("fields.vendorForEachSite");

                CookieBanner::getInstance()
                    ->getCookiesAndVendors()
                    ->editVendorForEachSite($vendorForEachSite);

                Craft::$app->getSession()->setSuccess("{$vendorName} vendor saved for all sites.");
                return $this->redirect('cookie-banner/cookies-and-vendors');

            } catch(Throwable $error) {
                Craft::error($error->getMessage(), __METHOD__);
                Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

                return $this->redirectToPostedUrl();
            }
        }

        $vendorForEachSite = CookieBanner::getInstance()
            ->getCookiesAndVendors()
            ->getEditVendorTableFieldData($vendorName);

        return $this->renderTemplate("cookie-banner/pages/_editVendor", [
            'vendorForEachSite' => $vendorForEachSite,
        ]);
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     */
    public function actionDeleteVendorForAllSites(): Response {
        $this->requirePermission("cookie-banner:remove-vendors");
        
        try {
            $vendorName = $this->request->getParam("vendorName");

            if (!$vendorName) throw new BadRequestHttpException('Missing body param vendorName');

            CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->deleteVendorForEachSite($vendorName);

            Craft::$app->getSession()->setSuccess("{$vendorName} vendor deleted for all sites.");
            return $this->redirect('cookie-banner/cookies-and-vendors');

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }

    /**
     * @return Response
     * @throws BadRequestHttpException
     * @throws ForbiddenHttpException
     * @throws MissingComponentException
     */
    public function actionBlacklistVendor(): Response {
        $this->requirePermission("cookie-banner:blacklist-vendors");

        try {
            $vendorName = $this->request->getBodyParam('vendorName');

            if (!$vendorName) throw new BadRequestHttpException('Missing body param vendorName');

            CookieBanner::getInstance()
                ->getCookiesAndVendors()
                ->blacklistVendorForEachSite($vendorName);

            Craft::$app->getSession()->setSuccess("{$vendorName} added to blacklist.");
            return $this->redirect('cookie-banner/cookies-and-vendors');

        } catch(Throwable $error) {
            Craft::error($error->getMessage(), __METHOD__);
            Craft::$app->getSession()->setError('Error: ' . $error->getMessage());

            return $this->redirectToPostedUrl();
        }
    }

    public function actionGetVendorsChartData(): Response {
        $this->requirePermission('cookie-banner:access-cookies-and-vendors');

        $data = CookieBanner::getInstance()->getCookiesAndVendors()->getVendorsChartData();

        return $this->asJson($data);
    }

    public function actionSearchCookies() {
        $this->requirePermission("cookie-banner:add-cookies");
        
        $searchTerm = $this->request->getQueryParam("searchTerm");
        
        return $this->renderTemplate("cookie-banner/requests/_cookieDatabase", [
            'searchTerm' => $searchTerm,
        ]);
    }
            
    public function actionSearchVendors() {
        $this->requirePermission("cookie-banner:add-vendors");

        $searchTerm = $this->request->getQueryParam("searchTerm");

        return $this->renderTemplate("cookie-banner/requests/_vendorDatabase", [
            'searchTerm' => $searchTerm,
        ]);
    }
}
