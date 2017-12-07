<?php
namespace Reverb\ReverbSync\Helper\Orders;
class Creation extends \Reverb\ReverbSync\Helper\Orders\Creation\Helper
{
    const ERROR_AMOUNT_PRODUCT_MISSING = 'The amount_product object, which is supposed contain the product\'s price, was not found';
    const ERROR_AMOUNT_TAX_MISSING = 'The amount_tax object, which is supposed contain the product\'s tax amount, was not found';
    const ERROR_INVALID_SKU = 'An attempt was made to create an order in magento for a Reverb order which had an invalid sku %s';
    const INVALID_CURRENCY_CODE = 'An invalid currency code %s was defined.';
    const EXCEPTION_UPDATE_STORE_NAME = 'An error occurred while setting the store name to %s for order with Reverb Order Id #%s: %s';
    const EXCEPTION_CONFIGURED_STORE_ID = 'An exception occurred while attempting to load the store with the configured store id of %s: %s';
    const EXCEPTION_REVERB_ORDER_CREATION_EVENT_OBSERVER = 'An exception occurred while firing the reverb_order_creation event for order with Reverb Order Number #%s: %s';

    const STORE_TO_SYNC_ORDERS_TO_CONFIG_PATH = 'ReverbSync/orders_sync/store_to_sync_order_to';

    const REVERB_ORDER_STORE_NAME = 'Reverb';

    protected $_ordersSyncHelper;

    protected $_scopeconfig;

    protected $_sourceStore;

    protected $_reverbLogger;

    protected $_customerHelper;

    protected $_addressHelper;

    protected $_productFactory;
    
    protected $_quoteManagement;
    protected $_customerFactory;
    protected $_customerRepository;
    protected $_orderService;

    protected $_productRepository;

    protected $_cartRepositoryInterface;
    protected $_cartManagementInterface;
    protected $_shippingRate;

    protected $_cartmodel;

    public function __construct(
        \Reverb\ReverbSync\Helper\Orders\Sync $ordersSyncHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeconfig,
        \Reverb\ReverbSync\Model\Source\Store $sourceStore,
        \Reverb\ReverbSync\Model\Logger $reverblogger,
        \Reverb\ReverbSync\Helper\Orders\Creation\Address $addressHelper,
        \Reverb\ReverbSync\Helper\Orders\Creation\Customer $customerHelper,
        \Reverb\ReverbSync\Helper\Orders\Creation\Shipping $shippingHelper,
        \Reverb\ReverbSync\Helper\Orders\Creation\Payment $paymentHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Magento\Checkout\Model\Cart $cartmodel,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Quote\Model\Quote\Address\Rate $shippingRate
    ) {
        parent::__construct($reverblogger, $shippingHelper, $paymentHelper, $addressHelper, $customerHelper);
        $this->_ordersSyncHelper = $ordersSyncHelper;
        $this->_scopeconfig = $scopeconfig;
        $this->_sourceStore = $sourceStore;
        $this->_reverbLogger = $reverblogger;
        $this->_customerHelper = $customerHelper;
        $this->_addressHelper = $addressHelper;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_productRepository = $productRepository;
        $this->_quoteManagement = $quoteManagement;
        $this->_customerFactory = $customerFactory;
        $this->_customerRepository = $customerRepository;
        $this->_orderService = $orderService;
        $this->_cartmodel = $cartmodel;
        $this->_cartRepositoryInterface = $cartRepositoryInterface;
        $this->_cartManagementInterface = $cartManagementInterface;
        $this->_shippingRate = $shippingRate;
    }
    
    public function createMagentoOrder(\stdClass $reverbOrderObject)
    {
        // Including this check here just to ensure that orders aren't synced if the setting is disabled
        if (!$this->_ordersSyncHelper->isOrderSyncEnabled())
        {
            $exception_message = $this->_ordersSyncHelper->getOrderSyncIsDisabledMessage();
            throw new \Reverb\ReverbSync\Model\Exception\Deactivated\Order\Sync($exception_message);
        }
       
        $store = $this->_storeManager->getStore();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();

        $cart_id = $this->_cartManagementInterface->createEmptyCart();
        $cart = $this->_cartRepositoryInterface->get($cart_id);
        $cart->setStore($store);

        $reverb_order_number = $reverbOrderObject->order_number;
        

        if ($this->_ordersSyncHelper->isOrderSyncSuperModeEnabled())
        {
            // Process this quote as though we were an admin in the admin panel
            $cart->setIsSuperMode(true);
        }

        $productToAddToQuote = $this->_getProductToAddToQuote($reverbOrderObject);
        $qty = $reverbOrderObject->quantity;
        if (empty($qty))
        {
            $qty = 1;
        }
        $qty = intval($qty);
        $cart->addProduct($productToAddToQuote, $qty);


       // $this->_addReverbItemLinkToQuoteItem($cart, $reverbOrderObject);

        //$this->_addTaxAndCurrencyToQuoteItem($cart, $reverbOrderObject);
        $_objectmanager = \Magento\Framework\App\ObjectManager::getInstance();

        try{
            //if(!$customer->getEntityId()){
           /* $email = 'test'.uniqid().'@gmail.com';

            $websiteId  = $this->_storeManager->getWebsite()->getWebsiteId();

            // Instantiate object (this is the most important part)
            $customer   = $this->_customerFactory->create();
            $customer->setWebsiteId($websiteId);

            // Preparing data for new customer
            $customer->setEmail($email); 
            $customer->setFirstname("First Name");
            $customer->setLastname("Last name");
            $customer->setPassword("password");

            $customer->save(); 
            return true;*/

            //}
        }catch(\Exception $e){
            echo 'custom error = ';
            echo $e->getMessage();
            exit;
        }
        //$customer= $this->_customerRepository->getById($customer->getEntityId());
        $cart->setCurrency();
        $cart->assignCustomer($customer);
        //$this->_getCustomerHelper()->addCustomerToQuote($reverbOrderObject, $cart);
 
        $this->_getAddressHelper()->addOrderAddressAsShippingAndBillingToQuote($reverbOrderObject, $cart);
        $this->_getShippingHelper()->setShippingMethodAndRateOnQuote($reverbOrderObject, $cart);
        $this->_getPaymentHelper()->setPaymentMethodOnQuote($reverbOrderObject, $cart);

        exit;
        // The calling block will handle catching any exceptions occurring from the calls below
        $service = Mage::getModel('sales/service_quote', $cart);
        /* @var Mage_Sales_Model_Service_Quote $service */
        $service->submitAll();

        $order = $service->getOrder();

        $order->setReverbOrderId($reverb_order_number);

        $reverb_order_status = $reverbOrderObject->status;
        if (empty($reverb_order_status))
        {
            $reverb_order_status = 'created';
        }
        $order->setReverbOrderStatus($reverb_order_status);

        $order->save();

        try
        {
            // Update store name as adapter query for performance consideration purposes
            Mage::getResourceSingleton('reverbSync/order')
                ->setReverbStoreNameByReverbOrderId($reverb_order_number, self::REVERB_ORDER_STORE_NAME);
        }
        catch(Exception $e)
        {
            // Log the exception but don't stop execution
            $error_message = __(self::EXCEPTION_UPDATE_STORE_NAME, self::REVERB_ORDER_STORE_NAME, $reverb_order_number, $e->getMessage());
            $this->_logOrderSyncError($error_message);
        }

        try
        {
            // Dispatch an event for clients to hook in to regarding order creation
            Mage::dispatchEvent('reverb_order_created',
                                array('magento_order_object' => $order, 'reverb_order_object' => $reverbOrderObject)
            );
        }
        catch(Exception $e)
        {
            // Log the exception but don't stop execution
            $error_message = __(self::EXCEPTION_REVERB_ORDER_CREATION_EVENT_OBSERVER, $reverb_order_number,
                                        $e->getMessage());
            $this->_logOrderSyncError($error_message);
        }

        $this->_getShippingHelper()->unsetOrderBeingSynced();
        $this->_getPaymentHelper()->unsetOrderBeingSynced();

        return $order;
    }

    protected function _getProductToAddToQuote(\stdClass $reverbOrderObject)
    {
        $sku = $reverbOrderObject->sku;
        $product = $this->_productRepository->get($sku);
        
        if ((!is_object($product)) || (!$product->getId()))
        {
            $error_message = __(self::ERROR_INVALID_SKU, $sku);
            throw new \Exception($error_message);
        }

        $amountProductObject = $reverbOrderObject->amount_product;
        if (!is_object($amountProductObject))
        {
            $error_message = __(self::ERROR_AMOUNT_PRODUCT_MISSING);
            throw new \Exception($error_message);
        }

        $amount = $amountProductObject->amount;
        if (empty($amount))
        {
            $amount = "0.00";
        }
        $product_cost = floatval($amount);
        $product->setPrice($product_cost);

        return $product;
    }

    protected function _addReverbItemLinkToQuoteItem($cart, $reverbOrderObject)
    {
        $items = $cart->getQuote()->getAllVisibleItems();
        foreach ($items as $key => $item) {
            if($item->getSku()==$reverbOrderObject->sku){
                if (isset($reverbOrderObject->_links->listing->href))
                {
                    $listing_api_url_path = $reverbOrderObject->_links->listing->href;
                    $item->setReverbItemLink($listing_api_url_path);
                }        
            }
        }
        
    }

    protected function _addTaxAndCurrencyToQuoteItem($quoteToBuild, $reverbOrderObject)
    {
        if (property_exists($reverbOrderObject, 'amount_tax'))
        {
            $amountTaxObject = $reverbOrderObject->amount_tax;
            if (is_object($amountTaxObject))
            {
                $tax_amount = $amountTaxObject->amount;
                if (empty($tax_amount))
                {
                    $tax_amount = "0.00";
                }
            }
            else
            {
                $tax_amount = "0.00";
            }
        }
        else
        {
            $tax_amount = "0.00";
        }
        foreach ($this->_cartmodel->getQuote()->getAllItems() as $key => $item) {
            echo $item->getId();
            echo '<br/>';
        }
        exit; 
        $quoteItem = $quoteToBuild->getItemsCollection()->getFirstItem();


        $quoteItem->setBaseTaxAmount($totalBaseTax);
        $totalTax = $quoteToBuild->getStore()->convertPrice($totalBaseTax);
        echo $quoteItem->setTaxAmount($totalTax);
        echo 'carttest';
        exit;
/*
        // The check to ensure this field is set has already been made at this point
        $amountProductObject = $reverbOrderObject->amount_product;
        $currency_code = $amountProductObject->currency;
        $currencyHelper = Mage::helper('ReverbSync/orders_creation_currency');
        if (!empty($currency_code))
        {
            if (!$currencyHelper->isValidCurrencyCode($currency_code))
            {
                $error_message = __(self::INVALID_CURRENCY_CODE, $currency_code);
                throw new \Exception($error_message);
            }
        }
        else
        {
            $currency_code = $currencyHelper->getDefaultCurrencyCode();
        }
        $currencyToForce = Mage::getModel('directory/currency')->load($currency_code);
        $quoteToBuild->setForcedCurrency($currencyToForce);*/
    }

    protected function _getStore()
    {
        // Check to see if the system configured store id is valid
        $system_configured_store_id = $this->_getSystemConfigurationStoreId();
        if ((!is_null($system_configured_store_id)) && ($system_configured_store_id !== false))
        {
            // If so return it
            return $system_configured_store_id;
        }

        return $store = $this->_storeManager->getStore();
        
    }

    protected function _getSystemConfigurationStoreId()
    {
        try
        {
            $configured_store_id = $this->_scopeconfig->getValue(self::STORE_TO_SYNC_ORDERS_TO_CONFIG_PATH);
            if ($this->_sourceStore->isAValidStoreId($configured_store_id))
            {
                return $configured_store_id;
            }
        }
        catch(Exception $e)
        {
            $error_message = __(self::EXCEPTION_CONFIGURED_STORE_ID, $configured_store_id, $e->getMessage());
            $this->_reverbLogger->logOrderSyncError($error_message);
        }

        return false;
    }
}
