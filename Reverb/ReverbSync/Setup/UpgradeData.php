<?php
/**
 * Reverb_ProcessQueue extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Reverb
 * @package   Reverb_ProcessQueue
 * @copyright Copyright (c) 2017
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Reverb\ReverbSync\Setup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;
 
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
 
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
 
        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.2') < 0) {
 
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'reverb_product_url',
            [
                'group' => 'General',
                'backend' => '',
                'frontend' => '',
                'label' => 'Reverb Product URL',
                'input' => 'hidden',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => 'false',
                'required' => false,
                'user_defined' => true,
                'apply_to' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false
            ]
        );
         $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'reverb_product_id',
            [
                'group' => 'General',
                'backend' => '',
                'frontend' => '',
                'label' => 'Reverb Product id',
                'input' => 'hidden',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => 'false',
                'required' => false,
                'user_defined' => true,
                'apply_to' => '',
                'visible_on_front' => false,
                'used_in_product_listing' => false
            ]
        );

          $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'reverb_sync',
            [
                'group' => 'General',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Sync to Reverb',
                'input' => 'select',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                'visible' => true,
                'required' => false,
                'default' => '1',
                'user_defined' => false,
                'apply_to' => '',
                'visible_on_front' => false,
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'used_in_product_listing' => false
            ]
        );

         $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'reverb_condition',
            [
                'type' => 'varchar',
                'input' => 'select',
                'label' => 'Reverb Condition',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'source' => '\Reverb\ReverbSync\Model\Source\Listing\Condition',
                'visible' => 1,
                'visible_on_front' => 0,
                'required' => 0,
                'used_in_product_listing' => 0,
                'is_configurable' => 0,
                'user_defined' => 1,
                'unique' => false,
                'filterable' => 0,
                'filterable_in_search' => 0,
                'group' => 'General'
            ]
        );

          $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'reverb_offers_enabled',
            [
                'label'                 => 'Reverb Accept Offers',
                'input'                 => 'select',
                'type'                  => 'int',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'source'                => '\Reverb\ReverbSync\Model\Source\Listing\Offer',
                'visible'               => true,
                'required'              => false,
                'user_defined'          => true,
                'default'               => '0',
                'used_in_product_listing' => false,
                'is_configurable'       => false,
                'visible_on_front'      => false,
                'unique'                => false,
                'group' => 'General'
            ]
        ); 
        $setup->endSetup();
        }
    }
}
