<?php
/**
 * Reverb Report admin controller
 *
 * @category    Reverb
 * @package     Reverb_Reports
 */
namespace Reverb\Reports\Controller\Adminhtml\Reports;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

    class Reverbreport extends \Magento\Backend\App\Action{

    	 /**
	     * @var PageFactory
	     */
	    protected $resultPageFactory;
	    
	    public function __construct(Context $context, PageFactory $resultPageFactory) {
	        parent::__construct($context);
	        $this->resultPageFactory = $resultPageFactory;
	    }

        public function execute(){
            $resultPage = $this->resultPageFactory->create();
	        $resultPage->setActiveMenu('Reverb_ReverbSync::reverb_listings_sync');
	        $resultPage->getConfig()->getTitle()->prepend((__('Reverb Listing Sync')));
	        return $resultPage;
        }
    }
?>