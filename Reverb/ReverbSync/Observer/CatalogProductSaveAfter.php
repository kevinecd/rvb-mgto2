<?php
namespace Reverb\ReverbSync\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class CatalogProductSaveAfter implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $_taskprocessor;
 
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Reverb\ReverbSync\Helper\Task\Processor $taskprocessor
    ) {
        $this->_objectManager = $objectManager;
        $this->_taskprocessor = $taskprocessor;
    }
 
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getProduct();
        $product_id = $product->getId();

        $reverbSyncTaskProcessor = $this->_taskprocessor;
        // @var $reverbSyncTaskProcessor Reverb_ReverbSync_Helper_Task_Processor 

        try
        {
            $test = $reverbSyncTaskProcessor->queueListingsSyncByProductIds(array($product_id));
        }
        catch(Exception $e)
        {
            echo 'error observer product save = ';
            echo $e->getMessage();
            exit;
            /*$error_message = $reverbSyncTaskProcessor->__(self::EXCEPTION_LISTING_SYNC_ON_PRODUCT_SAVE,
                                                            $product_id, $e->getMessage());

            $this->_getLogSingleton()->logListingSyncError($error_message);
            $exceptionToLog = new Exception($error_message);
            Mage::logException($exceptionToLog);*/
        }
    }
}