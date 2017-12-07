<?php
/**
 * Author: Sean Dunagan
 * Created: 9/11/15
 */
namespace Reverb\ReverbSync\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
 
class Edit extends Container
{
	/**
	 * Core registry
	 *
	 * @var \Magento\Framework\Registry
	 */
	protected $_coreRegistry = null;

	/**
	 * @param Context $context
	 * @param Registry $registry
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		Registry $registry,
		array $data = []
	) {
		$this->_coreRegistry = $registry;
		parent::__construct($context, $data);
	}

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_controller = 'adminhtml_category';
		$this->_blockGroup = 'Reverb_ReverbSync';

		parent::_construct();
		
		//$fetch_categories_route = $this->getAction()->getUriPathForAction('updateCategories');
        //$fetch_categories_url = $this->getUrl($fetch_categories_route);
        $updateReverbCategoryUrl = $this->getUrl("*/*/updatecategories");

        $this->buttonList->add('fetch_reverb_categories', array(
            'label'     => __('Update Reverb Categories'),
            'onclick'   => 'setLocation(\'' . $updateReverbCategoryUrl . '\')'
        ), -1);

		$this->buttonList->update('save', 'label', __('Save'));
		$this->buttonList->remove('back');
	}

	/**
	 * Retrieve text for header element depending on loaded news
	 * 
	 * @return string
	 */
	public function getHeaderText()
	{
		return __('Sync Reverb Categories');
	}

   /* public function __construct()
    {
        $controllerAction = $this->getAction();
        //$this->_objectId = $controllerAction->getObjectParamName();
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = $controllerAction->getModuleBlockGroupname();

        $fetch_categories_route = $this->getAction()->getUriPathForAction('updateCategories');
        $fetch_categories_url = $this->getUrl($fetch_categories_route);

        $this->_addButton('fetch_reverb_categories', array(
            'label'     => Mage::helper('ReverbSync')->__('Update Reverb Categories'),
            'onclick'   => 'setLocation(\'' . $fetch_categories_url . '\')'
        ), -1);

        parent::__construct();

        $this->_removeButton('delete');
        $this->_removeButton('back');
    }

    public function getFormActionUrl()
    {
        $uri_path = $this->getAction()->getUriPathForAction('save');
        return $this->getUrl($uri_path);
    }

    public function getHeaderText()
    {
        return Mage::helper('ReverbSync')->__('Sync Reverb Categories');
    }*/
}
