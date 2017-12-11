<?php
/**
 * Reverb Report admin controller
 *
 * @category    Reverb
 * @package     Reverb_Reports
 */
 
//require_once('Reverb/ProcessQueue/controllers/Adminhtml/ProcessQueue/Unique/IndexController.php');
namespace Reverb\ReverbSync\Controller\Adminhtml\Reverbsync\Listings;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Imagesync extends \Magento\Backend\App\Action
{

	const NOTICE_TASK_ACTION = 'The attempt to sync image file %s for product %s on Reverb has completed.';
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
		$resultPage->getConfig()->getTitle()->prepend((__('Reverb Listings Image Sync Tasks')));
		$gridBlock = $resultPage->getLayout()->createBlock('\Reverb\ReverbSync\Block\Adminhtml\Listings\Image\Unique\Index');
        $gridBlock->setNameInLayout('reverb_image_listing');
        $resultPage->addContent($gridBlock);
		return $resultPage;
	}
	/*public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_listings_image_unique_index'))
            ->_addContent($this->getLayout()->createBlock('ReverbSync/adminhtml_listings_image_task_unique_index'))
            ->renderLayout();
    }*/

}
?>