<?php
/**
 * Author: Sean Dunagan (https://github.com/dunagan5887)
 * Created: 11/7/15
 */

/**
 * Class Reverb_ReverbSync_Helper_Orders_Update_Paid
 */
class Reverb_ReverbSync_Helper_Orders_Update_Paid extends Reverb_ReverbSync_Helper_Orders_Update_Abstract
{
    const NO_PRODUCTS_INVOICED = 'The invoice did not contain any products';

    /**
     * {@inheritdoc}
     */
    public function getUpdateAction()
    {
        return 'invoiced';
    }

    /**
     * This method does not catch exceptions as it expects the calling block to catch them by design
     *
     * @param Mage_Sales_Model_Order $magentoOrder
     * @param string                 $reverb_order_status
     * @param stdClass               $orderUpdateArgumentsObject
     */
    public function executeMagentoOrderPaid(Mage_Sales_Model_Order $magentoOrder, $reverb_order_status,
                                            stdClass $orderUpdateArgumentsObject)
    {
        // Check if this order has already been fully invoiced
        if (!$this->_isOrderAlreadyFullyInvoiced($magentoOrder))
        {
            // Invoice the order
            $invoice = $this->_initInvoice($magentoOrder);
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();

            // Save the order and the invoice in a database transaction
            $transactionSave = Mage::getModel('core/resource_transaction');
            /* @var Mage_Core_Model_Resource_Transaction $transactionSave */
            $transactionSave->addObject($invoice)
                            ->addObject($invoice->getOrder());
            $transactionSave->save();
        }
    }

    /**
     * @param Mage_Sales_Model_Order $magentoOrder
     * @return Mage_Sales_Model_Order_Invoice
     * @throws Reverb_ReverbSync_Model_Exception_Data_Order_Update
     */
    protected function _initInvoice(Mage_Sales_Model_Order $magentoOrder)
    {
        if (!$magentoOrder->canInvoice())
        {
            $this->_inspectWhyCanNotUpdateAndThrowException($magentoOrder);
        }

        $magentoOrderService = Mage::getModel('sales/service_order', $magentoOrder);
        /* @var Mage_Sales_Model_Service_Order $magentoOrderService */
        $magentoInvoice = $magentoOrderService->prepareInvoice(array());
        if (!$magentoInvoice->getTotalQty())
        {
            $this->_throwCanNotUpdateException($magentoOrder, self::NO_PRODUCTS_INVOICED);
        }

        return $magentoInvoice;
    }
}
