<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\assetbundles\printshop;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use craft\web\assets\vue\VueAsset;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class PrintShopAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@angellco/printshop/assetbundles/printshop/dist";

        $this->depends = [
            CpAsset::class,
            VueAsset::class,
        ];

        $this->js = [
            'js/chunk-vendors.js',
            'js/app.js',
        ];

        $this->css = [
            'css/app.css',
        ];

        parent::init();
    }
}
