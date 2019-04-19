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

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\commerce\elements\Order;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\twig\variables\CraftVariable;

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


        // Hook in to the order edit page
        Craft::$app->view->hook('cp.commerce.order.edit', function(array &$context) {
            $context['tabs'][] = [
                'label' => Craft::t('print-shop', 'Print Shop'),
                'url' => '#printShopTab',
                'class' => null
            ];
        });
        Craft::$app->view->hook('cp.commerce.order.edit.main-pane', function(array &$context) {
//            Craft::$app->view->registerAssetBundle("angellco\\printshop\\assetbundles\\printshop\\PrintShopAsset");
            return Craft::$app->view->renderTemplate('print-shop/orders/_edit-pane', $context);
        });

// XXX not working!
        // Register the proofing status attribute for the order index
        Event::on(Order::class, Element::EVENT_REGISTER_TABLE_ATTRIBUTES, function(RegisterElementTableAttributesEvent $event) {
            $event->tableAttributes['proofingStatus'] = ['label' => Craft::t('print-shop', 'Proofing Status')];
        });
        Event::on(Order::class, Element::EVENT_SET_TABLE_ATTRIBUTE_HTML, function(SetElementTableAttributeHtmlEvent $event) {
            if ($event->attribute === 'proofingStatus') {
                /** @var Order $order */
                $order = $event->sender;

                $event->html = PrintShop::$plugin->proofs->getProofingStatusHtml($order);

                // Prevent other event listeners from getting invoked
                $event->handled = true;
            }
        });
// XXX not working!

        // Load up the Variable
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('printShop', PrintShopVariable::class);
            }
        );


        // After install event
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


        // Log when the plugin init has run
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

        $settings = $this->getSettings();

        // Volumes
        $volumes = Craft::$app->getVolumes()->getAllVolumes();

        // Statuses
        $proofsSentStatus = null;
        if ($settings->proofsSentStatusUid) {
            $proofsSentStatusId = Db::idByUid('{{%commerce_orderstatuses}}', $settings->proofsSentStatusUid);
            $proofsSentStatus = PrintShop::$commerce->getOrderStatuses()->getOrderStatusById($proofsSentStatusId);
        }
        if (!$proofsSentStatus) {
            $proofsSentStatus = PrintShop::$commerce->getOrderStatuses()->getDefaultOrderStatus();
        }

        $proofsApprovedStatus = null;
        if ($settings->proofsApprovedStatusUid) {
            $proofsApprovedStatusId = Db::idByUid('{{%commerce_orderstatuses}}', $settings->proofsApprovedStatusUid);
            $proofsApprovedStatus = PrintShop::$commerce->getOrderStatuses()->getOrderStatusById($proofsApprovedStatusId);
        }
        if (!$proofsApprovedStatus) {
            $proofsApprovedStatus = PrintShop::$commerce->getOrderStatuses()->getDefaultOrderStatus();
        }

        $proofsRejectedStatus = null;
        if ($settings->proofsRejectedStatusUid) {
            $proofsRejectedStatusId = Db::idByUid('{{%commerce_orderstatuses}}', $settings->proofsRejectedStatusUid);
            $proofsRejectedStatus = PrintShop::$commerce->getOrderStatuses()->getOrderStatusById($proofsRejectedStatusId);
        }
        if (!$proofsRejectedStatus) {
            $proofsRejectedStatus = PrintShop::$commerce->getOrderStatuses()->getDefaultOrderStatus();
        }

        // Emails
        $commerceEmails = PrintShop::$commerce->getEmails()->getAllEmails();

        return Craft::$app->view->renderTemplate(
            'print-shop/settings',
            [
                'settings' => $settings,
                'volumes' => $volumes,
                'proofsSentStatus' => $proofsSentStatus,
                'proofsApprovedStatus' => $proofsApprovedStatus,
                'proofsRejectedStatus' => $proofsRejectedStatus,
                'commerceEmails' => $commerceEmails
            ]
        );
    }
}
