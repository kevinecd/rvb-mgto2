<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */
namespace Reverb\ReverbSync\Block\Adminhtml\Listings\Imagesync;

use Magento\Store\Model\Store;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	
	/**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;
	
     /**
     * @var \Reverb\ProcessQueue\Model\Resource\Task\Unique\Collection
     */
    protected $_imageSyncCollection;
	
	/**
     * @var \Reverb\ProcessQueue\Model\Source\Task\Status
     */
    protected $_taskStatus;
	
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Reverb\ProcessQueue\Model\Resource\Task\Unique\Collection
     * @param \Reverb\ProcessQueue\Model\Source\Task\Status
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Product\Type $type,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $status,
        \Magento\Catalog\Model\Product\Visibility $visibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Reverb\ProcessQueue\Model\Resource\Taskresource\Unique\Collection $_imageSyncCollection,
        \Reverb\ProcessQueue\Model\Source\Task\Status $_taskStatus,
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->moduleManager = $moduleManager;
        $this->_imageSyncCollection = $_imageSyncCollection;
        $this->_taskStatus = $_taskStatus;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reverbimagesyncGrid');
        $this->setDefaultSort('task_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        //$this->setVarNameFilter('product_filter');
    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $store = $this->_getStore();
        $collection = $this->_imageSyncCollection->addFieldToFilter('code','listing_image_sync');
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        return $this;
    }
	
    protected function _prepareColumns()
    {
        $this->addColumn(
			'sku',
			[
				'header'    => __('Sku'),
				'align'     => 'left',
				'type'      => 'text',
				//'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_listings_image_sku',
				'filter'    => false,
				'sortable'  => false
			]
		);

        $this->addColumn(
			'subject_id',
			[
				'header'    => __('Image Filename'),
				'align'     => 'left',
				'type'      => 'text',
				'index'  => 'subject_id'
			]
		);

        $this->addColumn(
			'status',
			[
				'header'    => __('Status'),
				'align'     => 'left',
				'index'     => 'status',
				'type'      => 'options',
				'options'   => $this->_taskStatus->getOptionArray()
				//'options'   => Mage::getModel('reverb_process_queue/source_task_status')->getOptionArray()
			]
		);

        $this->addColumn(
			'status_message',
			[
				'header'    => __('Status Message'),
				'align'     => 'left',
				'index'     => 'status_message',
				'type'      => 'text'
			]
		);

        $this->addColumn(
			'created_at',
			[
				'header'    => __('Created At'),
				'align'     => 'left',
				'index'     => 'created_at',
				'type'      => 'datetime'
			]
		);

        $this->addColumn(
			'last_executed_at',
			[
				'header'    => __('Last Executed At'),
				'align'     => 'left',
				'index'     => 'last_executed_at',
				'type'      => 'datetime',
				//'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_datetime',
			]
		);

        $this->addColumn(
			'action',
			[
				'header'    => __('Action'),
				'width'     => '50px',
				'type'      => 'action',
				'getter'    => 'getId',
				'renderer'  => '\Reverb\ReverbSync\Block\Adminhtml\Widget\Grid\Column\Renderer\Listings\Image\Task\Action',
				'filter'    => false,
                'sortable'  => false,
				//'task_controller' => 'ReverbSync_orders_sync_unique'
			]
		);

        return parent::_prepareColumns();
    }

    /*public function setCollection($collection)
    {
        $collection->addCodeFilter('listing_image_sync');
        parent::setCollection($collection);
    }*/
	
	/**
     * @return string
     */
    public function getGridUrl()
	{
		return $this->getUrl('*/*/ajaxGrid', array('_current'=>true));
    }
}
