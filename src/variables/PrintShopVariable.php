<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\variables;

use angellco\printshop\PrintShop;

use Craft;
use craft\errors\VolumeException;
use craft\helpers\Db;
use craft\models\VolumeFolder;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class PrintShopVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Returns a File model for a given LineItem ID
     *
     * @param int $lineItemId
     *
     * @return \angellco\printshop\models\File|bool
     */
    public function getFile($lineItemId)
    {
        return PrintShop::$plugin->files->getFileByLineItemId($lineItemId);
    }

    /**
     * Returns a Proof model by its uid
     *
     * @param string $uid
     *
     * @return mixed
     */
    public function getProof($uid)
    {
        return PrintShop::$plugin->proofs->getProofByUid($uid);
    }

    /**
     * Returns the Proofs folder for a given order number.
     *
     * @param $orderNumber
     *
     * @return string
     * @throws VolumeException
     * @throws \Throwable
     * @throws \craft\errors\AssetConflictException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\VolumeObjectExistsException
     * @throws \yii\base\Exception
     */
    public function getProofsFolderSourceForOrder($orderNumber)
    {
        // Get the proofs folder
        $folders = PrintShop::$plugin->folders->getFoldersForOrder($orderNumber);
        /** @var VolumeFolder $folder */
        $folder = $folders['proofs'];
        $folderPath = 'folder:' . $folder->uid;

        // Construct the path
        while ($folder->parentId && $folder->volumeId !== null) {
            $parent = $folder->getParent();
            $folderPath = 'folder:' . $parent->uid . '/' . $folderPath;
            $folder = $parent;
        }

        return $folderPath;
    }

    /**
     * Returns the Files folder for a given order number.
     *
     * @param $orderNumber
     *
     * @return string
     * @throws VolumeException
     * @throws \Throwable
     * @throws \craft\errors\AssetConflictException
     * @throws \craft\errors\ElementNotFoundException
     * @throws \craft\errors\VolumeObjectExistsException
     * @throws \yii\base\Exception
     */
    public function getFilesFolderSourceForOrder($orderNumber)
    {
        // Get the proofs folder
        $folders = PrintShop::$plugin->folders->getFoldersForOrder($orderNumber);
        /** @var VolumeFolder $folder */
        $folder = $folders['files'];
        $folderPath = 'folder:' . $folder->uid;

        // Construct the path
        while ($folder->parentId && $folder->volumeId !== null) {
            $parent = $folder->getParent();
            $folderPath = 'folder:' . $parent->uid . '/' . $folderPath;
            $folder = $parent;
        }

        return $folderPath;
    }

}
