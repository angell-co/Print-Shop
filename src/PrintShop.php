<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop;

use angellco\printshop\services\Files as FilesService;
use angellco\printshop\services\Proofs as ProofsService;
use angellco\printshop\variables\PrintShopVariable;
use angellco\printshop\models\Settings;
use angellco\printshop\fields\PrintShopField as PrintShopFieldField;

use Craft;
use craft\base\Plugin;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use craft\commerce\Plugin as Commerce;

use yii\base\Event;

/**
 * Class PrintShop
 *
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 *
 * @property  FilesService $files
 * @property  ProofsService $proofs
 */
class PrintShop extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var PrintShop
     */
    public static $plugin;

    /**
     * @var \craft\commerce\Plugin
     */
    public static $commerce;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '2.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;
        self::$commerce = Commerce::getInstance();

//        Event::on(
//            Fields::class,
//            Fields::EVENT_REGISTER_FIELD_TYPES,
//            function (RegisterComponentTypesEvent $event) {
//                $event->types[] = PrintShopFieldField::class;
//            }
//        );

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('printShop', PrintShopVariable::class);
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // Redirect to settings
                    $request = Craft::$app->getRequest();
                    if ($request->isCpRequest) {
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('settings/plugins/print-shop'))->send();
                    }
                }
            }
        );

        Craft::info(
            Craft::t(
                'print-shop',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'print-shop/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
