<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\assetbundles\PrintShop;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

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
        ];

        $this->js = [
            'js/PrintShop.js',
        ];

        $this->css = [
            'css/PrintShop.css',
        ];

        parent::init();
    }
}
