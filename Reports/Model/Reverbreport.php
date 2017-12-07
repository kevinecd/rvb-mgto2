<?php
/**
 * Reverb Report model
 *
 * @category    Reverb
 * @package     Reverb_Reports

 */
namespace Reverb\Reports\Model;
use Magento\Framework\Model\AbstractModel;

class Reverbreport extends AbstractModel
{

    const STATUS_SUCCESS = 1;

    // Table Field Name Constants
    const PRODUCT_ID_FIELD = 'product_id';
    const TITLE_FIELD = 'title';
    const PRODUCT_SKU_FIELD = 'product_sku';
    const INVENTORY_FIELD = 'inventory';
    const REV_URL_FIELD = 'rev_url';
    const STATUS_FIELD = 'status';
    const SYNC_DETAILS_FIELD = 'sync_details';
    const LAST_SYNCED_FIELD = 'last_synced';

    protected $_api_content_fields_to_set = array(
        self::TITLE_FIELD => self::TITLE_FIELD,
        self::INVENTORY_FIELD => self::INVENTORY_FIELD
    );

   
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY = 'reverb_reports_reverbreport';
    const CACHE_TAG = 'reverb_reports_reverbreport';
    /**
     * Prefix of model events names
     * @var string
     */
    protected $_eventPrefix = 'reverb_reports_reverbreport';

    protected $_eventObject = 'reverbreport';

    protected $_datetime;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime
    ) {
        $this->_datetime = $datetime;
        $this->_registry = $registry;
        parent::__construct($context, $registry);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Reverb\Reports\Model\Resource\Reverbreport::class); 
    }

    public function populateWithDataFromListingWrapper(\Reverb\ReverbSync\Model\Wrapper\Listing $listingWrapper)
    {
        $data_to_add_on_object = array();

        // Fields currently being saved to the reports table:
        //      product_id,title,product_sku ,inventory,rev_url,status,sync_details,last_synced
        $magentoProduct = $listingWrapper->getMagentoProduct();
        $data_to_add_on_object[self::PRODUCT_ID_FIELD] = $magentoProduct->getId();
        $data_to_add_on_object[self::PRODUCT_SKU_FIELD] = $magentoProduct->getSku();

        $api_call_content_data_array = $listingWrapper->getApiCallContentData();
        foreach ($this->_api_content_fields_to_set as $field)
        {
            $data_to_add_on_object = $this->_addFieldIfNotEmpty($field, $data_to_add_on_object, $api_call_content_data_array);
        }

        $data_to_add_on_object['status'] = $listingWrapper->getStatus();
        $data_to_add_on_object['sync_details'] = $listingWrapper->getSyncDetails();
        $data_to_add_on_object['rev_url'] = $listingWrapper->getReverbWebUrl();
        $this->addData($data_to_add_on_object);
        $this->updateSyncTimeToCurrentTime();
    }

    protected function _addFieldIfNotEmpty($field, array $array_to_add_to, array $source_data_array)
    {
        if (isset($source_data_array[$field]))
        {
            $value = $source_data_array[$field];
            $array_to_add_to[$field] = $value;
        }

        return $array_to_add_to;
    }

    public function updateSyncTimeToCurrentTime()
    {
        $current_date = $this->_datetime->gmtDate();
        $this->setData(self::LAST_SYNCED_FIELD, $current_date);
        return $this;
    }

}
