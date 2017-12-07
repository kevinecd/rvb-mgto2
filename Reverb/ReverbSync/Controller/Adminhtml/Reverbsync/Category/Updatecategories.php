<?php
namespace Reverb\ReverbSync\Controller\Adminhtml\Reverbsync\Category;

class Updatecategories extends \Magento\Backend\App\Action
{
	const EXCEPTION_UPDATING_REVERB_CATEGORIES = 'An exception occurred while updating the Reverb categories in the system: %s';
    const SUCCESS_UPDATED_LISTINGS = 'Reverb category update completed';
	
	/**
     * @param \Magento\Backend\App\Action\Context $context
     
    */
	
	public function __construct(
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }
	
	/*
    * Send all selected customers to emma , send all if none selected
    */
    public function execute()
    {
		exit('Updatecategories action called');
		try
        {
            $categoryUpdateSyncHelper = Mage::helper('ReverbSync/sync_category_update');
            /* @var $categoryUpdateSyncHelper Reverb_ReverbSync_Helper_Sync_Category_Update */
            $categoryUpdateSyncHelper->updateReverbCategoriesFromApi();
        }
        catch(Exception $e)
        {
            $error_message = $this->__(self::EXCEPTION_UPDATING_REVERB_CATEGORIES, $e->getMessage());
            Mage::getSingleton('reverbSync/log')->logCategoryMappingError($error_message);
            $this->_setSessionErrorAndRedirect($error_message);
        }

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__(self::SUCCESS_UPDATED_LISTINGS));

        $this->_redirect('*/*/index');
	}
}