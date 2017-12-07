<?php
namespace Reverb\ReverbSync\Helper\Orders\Creation;
class Payment extends \Magento\Framework\App\Helper\AbstractHelper//extends \Reverb\ReverbSync\Helper\Orders\Creation\Sync
{
    protected $_payment_method_code = 'reverbpayment';


    public function setPaymentMethodOnQuote($reverbOrderObject, $quoteToBuild)
    {
        $this->_setOrderBeingSyncedInRegistry($reverbOrderObject);

        $quoteToBuild->getShippingAddress()->setPaymentMethod($this->_payment_method_code);
        $quoteToBuild->getShippingAddress()->setCollectShippingRates(true);

        $payment = $quoteToBuild->getPayment();
        $payment->importData(array('method' => $this->_payment_method_code));
        $quoteToBuild->save();
        $quoteToBuild->setTotalsCollectedFlag(false);
        $quoteToBuild->collectTotals();
        $quoteToBuild->save();
    }
}
