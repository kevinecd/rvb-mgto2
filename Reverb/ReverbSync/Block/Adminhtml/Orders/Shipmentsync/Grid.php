<?php
/**
 * Author: Sean Dunagan
 * Created: 9/25/15
 */
namespace Reverb\ReverbSync\Block\Adminhtml\Orders\Shipmentsync;

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
    protected $_shipmentSyncCollection;
	
	/**
     * @var \Reverb\ProcessQueue\Model\Source\Task\Status
     */
    protected $_taskStatus;
	
	/**
     * @var \Reverb\ReverbSync\Model\Source\Unique\Task\Codes
     */
    protected $_taskCode;
	
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
     * @param \Reverb\ReverbSync\Model\Source\Unique\Task\Codes
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
        \Reverb\ProcessQueue\Model\Resource\Taskresource\Unique\Collection $_shipmentSyncCollection,
        \Reverb\ProcessQueue\Model\Source\Task\Status $_taskStatus,
		\Reverb\ReverbSync\Model\Source\Unique\Task\Codes $_taskCode,
        array $data = []
    ) {
        $this->_websiteFactory = $websiteFactory;
        $this->_setsFactory = $setsFactory;
        $this->_productFactory = $productFactory;
        $this->_type = $type;
        $this->_status = $status;
        $this->_visibility = $visibility;
        $this->moduleManager = $moduleManager;
        $this->_shipmentSyncCollection = $_shipmentSyncCollection;
		$this->_taskStatus = $_taskStatus;
        $this->_taskCode = $_taskCode;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reverbshipmentsyncGrid');
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
        $collection = $this->_shipmentSyncCollection->addFieldToFilter('code','shipment_tracking_sync');
        $this->setCollection($collection);
        
        parent::_prepareCollection();
        return $this;
    }
	
	protected function _prepareColumns()
    {
		$this->addColumn(
			'code',
			[
				'header'    => __('Sync Code'),
				'align'     => 'left',
				'index'     => 'code',
				'type'      => 'options',
				'options'   => $this->_taskCode->getOptionArray()
			]
		);

        $this->addColumn(
			'unique_id',
			[
				'header'    => __('Reverb Order ID'),
				'align'     => 'left',
				'index'     => 'unique_id',
				//'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_order_id',
                'type'      => 'text'
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
			]
		);

        $this->addColumn(
			'sku',
			[
				'header'    => __('Sku'),
				'align'     => 'left',
				'type'      => 'text',
				'renderer'  => '\Reverb\ReverbSync\Block\Adminhtml\Widget\Grid\Column\Renderer\Order\Product\Sku',
				'filter'    => false,
				'sortable'  => false
			]
		);

        $this->addColumn(
			'name',
			[
				'header'    => __('Name'),
				'align'     => 'left',
				'type'      => 'text',
				'renderer'  => '\Reverb\ReverbSync\Block\Adminhtml\Widget\Grid\Column\Renderer\Order\Product\Name',
				'filter'    => false,
				'sortable'  => false
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
				//'renderer'  => 'ReverbSync/adminhtml_widget_grid_column_renderer_order_task_action',
				'filter'    => false,
				'sortable'  => false,
				'task_controller' => 'ReverbSync_orders_sync_unique'
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
