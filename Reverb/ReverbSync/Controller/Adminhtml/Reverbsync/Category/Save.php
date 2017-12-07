<?php
namespace Reverb\ReverbSync\Controller\Adminhtml\Reverbsync\Category;

class Save extends \Magento\Backend\App\Action
{
	const ERROR_SUBMISSION_NOT_POST = 'There was an error with your submission. Please try again.';
    const EXCEPTION_CATEGORY_MAPPING = 'An error occurred while attempting to set the Reverb-Magento category mapping: %s';
	
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
		exit('Save action called');
		
		if (!$this->getRequest()->isPost())
        {
            $error_message = self::ERROR_SUBMISSION_NOT_POST;
            $this->_getAdminHelper()->throwRedirectException($error_message);
        }

        $post_array = $this->getRequest()->getPost();

        try
        {
            $category_map_form_element_name = $this->_getCategorySyncHelper()
                                                    ->getMagentoReverbCategoryMapElementArrayName();
            $category_mapping_array = isset($post_array[$category_map_form_element_name])
                                        ? $post_array[$category_map_form_element_name] : null;
            if (!is_array($category_mapping_array) || empty($category_mapping_array))
            {
                // This shouldn't occur, but account for the fact where it does
                $error_message = self::ERROR_SUBMISSION_NOT_POST;
                throw new Exception($error_message);
            }

            $this->_getCategorySyncHelper()->processMagentoReverbCategoryMapping($category_mapping_array);
        }
        catch(Exception $e)
        {
            $error_message = sprintf(self::EXCEPTION_CATEGORY_MAPPING, $e->getMessage());

            Mage::getSingleton('reverbSync/log')->logCategoryMappingError($error_message);
            $this->_setSessionErrorAndRedirect($error_message);
        }

        $this->_redirect('*/*/index');
	}
}