<?php
/**
 * Print Shop plugin for Craft CMS 3.x
 *
 * Everything you need to build a print shop with Craft Commerce 2.
 *
 * @link      https://angell.io
 * @copyright Copyright (c) 2019 Angell & Co
 */

namespace angellco\printshop\migrations;

use angellco\printshop\models\Proof;
use angellco\printshop\PrintShop;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Angell & Co
 * @package   PrintShop
 * @since     2.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%printshop_files}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%printshop_files}}',
                [
                    'id' => $this->primaryKey(),
                    'assetId' => $this->integer(),
                    'lineItemId' => $this->integer(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%printshop_proofs}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%printshop_proofs}}',
                [
                    'id' => $this->primaryKey(),
                    'fileId' => $this->integer(),
                    'assetId' => $this->integer(),
                    'status' => $this->enum('status', [Proof::STATUS_NEW, Proof::STATUS_APPROVED, Proof::STATUS_REJECTED])->notNull()->defaultValue(Proof::STATUS_NEW),
                    'staffNotes' => $this->text(),
                    'customerNotes' => $this->text(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%printshop_files}}', 'assetId', false);
        $this->createIndex(null, '{{%printshop_files}}', 'lineItemId', false);
        $this->createIndex(null, '{{%printshop_proofs}}', 'fileId', false);
        $this->createIndex(null, '{{%printshop_proofs}}', 'assetId', false);
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%printshop_files}}', ['assetId'], '{{%assets}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%printshop_files}}', ['lineItemId'], '{{%commerce_lineitems}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%printshop_proofs}}', ['fileId'], '{{%printshop_files}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%printshop_proofs}}', ['assetId'], '{{%assets}}', ['id'], 'CASCADE');
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%printshop_files}}');
        $this->dropTableIfExists('{{%printshop_proofs}}');
    }
}
