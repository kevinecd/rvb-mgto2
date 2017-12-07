<?php


namespace Reverb\ReverbSync\Controller\Adminhtml\ReverbSync;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\LayoutFactory;

abstract class BaseController extends Action
{
    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param Action\Context $context
     * @param LayoutFactory $layoutFactory
     * @param Builder $productBuilder
     * @param Helper $helper
     */
    public function __construct(
        Action\Context $context,
        LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        parent::__construct($context);
    }

    protected $_adminHelper = null;

    abstract public function getBlockToShow();

    abstract public function getControllerActiveMenuPath();

    abstract public function getControllerDescription();



    public function execute(){
        echo 'testigone ';exit; 
    }

    public function indexAction()
    {
        echo 'testttt'; exit;
 
        $module_helper_groupname = $this->getModuleHelperGroupname();
        $module_description = $this->getControllerDescription();

        $module_block_classname = $this->getBlockToShow();

        $this->loadLayout()
            ->_setActiveMenuValue()
            ->_setSetupTitle(Mage::helper($module_helper_groupname)->__($module_description))
            ->_addBreadcrumb()
            ->_addBreadcrumb(Mage::helper($module_helper_groupname)->__($module_description), Mage::helper($module_helper_groupname)->__($module_description))
            ->_addContent($this->getLayout()->createBlock($module_block_classname))
            ->renderLayout();
    }

    public function getModuleHelperGroupname()
    {
        return "ReverbSync";
    }

    public function getModuleBlockGroupname()
    {
        return "ReverbSync";
    }

    protected function _isAllowed()
    {
        return true; // Mage::getSingleton('admin/session')->isAllowed($this->getControllerActiveMenuPath());
    }

    protected function _addBreadcrumb($label = null, $title = null, $link=null)
    {
        if (is_null($label))
        {
            $module_groupname = $this->getModuleHelperGroupname();
            $module_description = $this->getControllerDescription();
            $label = Mage::helper($module_groupname)->__($module_description);
        }
        if (is_null($title))
        {
            $module_groupname = $this->getModuleHelperGroupname();
            $module_description = $this->getControllerDescription();
            $title = Mage::helper($module_groupname)->__($module_description);
        }
        return parent::_addBreadcrumb($label, $title, $link);
    }

    protected function _setActiveMenuValue()
    {
        return parent::_setActiveMenu($this->getControllerActiveMenuPath());
    }

    protected function _setSetupTitle($title)
    {
        try
        {
            $this->_title($title);
        }
        catch (Exception $e)
        {
            //Mage::logException($e);
        }
        return $this;
    }

    /**
     * @return Reverb_ReverbSync_Helper_Admin
     */
    protected function _getAdminHelper()
    {
        if (is_null($this->_adminHelper))
        {
            //$this->_adminHelper = Mage::helper('ReverbSync/admin');
        }

        return $this->_adminHelper;
    }
}
