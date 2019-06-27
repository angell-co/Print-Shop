<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\services;

use angellco\printshop\PrintShop;
use angellco\printshop\models\Settings;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\models\VolumeFolder;
use yii\base\Exception;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Folders extends Component
{

    /**
     * Gets (and creates if needed) the folders for a given order number, or the
     * current cart if no order number was passed.
     *
     * @param null $orderNumber
     *
     * @return array
     * @throws Exception
     * @throws \Throwable
     * @throws \craft\errors\AssetConflictException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\VolumeObjectExistsException
     */
    public function getFoldersForOrder($orderNumber = null)
    {
        $assets = Craft::$app->getAssets();

        // Get the plugin settings so we have access to the volume info
        /** @var Settings $settings */
        $settings = PrintShop::$plugin->getSettings();
        $volumeId = Db::idByUid('{{%volumes}}', $settings->volumeUid);

        $volumeSubpath = $settings->volumeSubpath ? $settings->volumeSubpath.'/' : '';


        /**
         * Make a folder for the order using the order hash
         */
        if ($orderNumber) {
            $order = PrintShop::$commerce->getOrders()->getOrderByNumber($orderNumber);
        } else {
            $order = PrintShop::$commerce->getCarts()->getCart();
        }
        $rootFolder = $assets->getRootFolderByVolumeId($volumeId);
        if (!$rootFolder) {
            throw new Exception(Craft::t('print-shop', 'Couldn’t get root folder.'));
        }

        // Try for an order folder - in case it already exists
        $orderFolder = $assets->findFolder([
            'parentId' => $rootFolder->id,
            'name' => $order->shortNumber,
        ]);


        // If we didn’t get one, create it
        if ($orderFolder === null) {
            $folderModel = new VolumeFolder();
            $folderModel->name = $order->shortNumber;
            $folderModel->parentId = $rootFolder->id;
            $folderModel->volumeId = $rootFolder->volumeId;
            $folderModel->path = $volumeSubpath.$order->shortNumber.'/';

            $assets->createFolder($folderModel);

            $orderFolder = $folderModel;
        }

        // Final order folder check
        if (!$orderFolder) {
            throw new Exception(Craft::t('print-shop', 'Unable to upload files at this time.'));
        }


        /**
         * Make a folder for the customer files inside the order folder
         */
        // Try for the customer files folder - in case it already exists
        $customerFilesFolder = $assets->findFolder([
            'parentId' => $orderFolder->id,
            'name' => Craft::t('print-shop','Customer Files'),
        ]);

        // Check if we got one
        if (!$customerFilesFolder) {
            // We didn’t, so create it
            $folderModel = new VolumeFolder();
            $folderModel->name = Craft::t('print-shop','Customer Files');
            $folderModel->parentId = $orderFolder->id;
            $folderModel->volumeId = $orderFolder->volumeId;
            $folderModel->path = $orderFolder->path.Craft::t('print-shop','Customer-Files').'/';

            $assets->createFolder($folderModel);

            $customerFilesFolder = $folderModel;
        }

        // Final customer files folder check
        if (!$customerFilesFolder) {
            throw new Exception(Craft::t('print-shop', 'Unable to upload files at this time.'));
        }


        /**
         * Also make a folder for the proofs whilst we’re here
         */
        // Try for the proofs files folder - in case it already exists
        $proofsFolder = $assets->findFolder([
            'parentId' => $orderFolder->id,
            'name' => Craft::t('print-shop','Proofs'),
        ]);

        // Check if we got one
        if (!$proofsFolder) {
            // We didn’t, so create it
            $folderModel = new VolumeFolder();
            $folderModel->name = Craft::t('print-shop','Proofs');
            $folderModel->parentId = $orderFolder->id;
            $folderModel->volumeId = $orderFolder->volumeId;
            $folderModel->path = $orderFolder->path.Craft::t('print-shop','Proofs').'/';

            $assets->createFolder($folderModel);

            $proofsFolder = $folderModel;
        }

        // Final proofs folder check
        if (!$proofsFolder) {
            throw new Exception(Craft::t('print-shop', 'Unable to upload files at this time.'));
        }

        // Return the set of folders
        return [
            'orders' => $orderFolder,
            'files' => $customerFilesFolder,
            'proofs' => $proofsFolder
        ];
    }

}