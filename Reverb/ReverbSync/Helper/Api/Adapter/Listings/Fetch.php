<?php
namespace Reverb\ReverbSync\Helper\Api\Adapter\Listings;
class Fetch implements \Reverb\ReverbSync\Helper\Api\Adapter\Interfaceclass//extends \Reverb\ReverbSync\Helper\Data 
{
    const ERROR_INVALID_SKU = 'An attempt was made to fetch the Reverb listing for an invalid sku: %s';

    protected $_reverbLog;

    protected $_productRepository;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Reverb\ReverbSync\Model\Log $reverbLog,
        \Reverb\ReverbSync\Helper\Data $reverbSyncHelper
    ) {
        $this->_productRepository = $productRepository;
        $this->_reverbLog = $reverbLog;
        $this->_reverbSyncHelper = $reverbSyncHelper;
    }

    public function getUpdatePutLinkBySku($magento_sku)
    {
        $fetch_listings_response = $this->executeFetchListingsApiCall($magento_sku);
        $first_listing_in_response = $this->_getFirstListingInResponse($fetch_listings_response);
        if (is_array($first_listing_in_response))
        {
            $update_put_link_uri_path = $this->_getSelfUrlFromListingResponseArray($first_listing_in_response);
            $update_put_link =  $this->_reverbSyncHelper->getReverbBaseUrl() . $update_put_link_uri_path;
            return $update_put_link;
        }

        return null;
    }

    public function executeFetchListingsApiCall($magento_sku)
    {
        $magento_entity_id = $this->_validateSku($magento_sku);
        $api_endpoint_url = $this->_getFetchListingsEnpointUrl($magento_sku);
        $curlResource = $this->_reverbSyncHelper->getCurlResource($api_endpoint_url);
        $json_response = $curlResource->read();
        $response_as_array = $this->_reverbSyncHelper->processCurlRequestResponse($json_response, $curlResource);
        return $response_as_array;
    }

    protected function _getFirstListingInResponse($fetch_listings_response)
    {
        if (isset($fetch_listings_response['listings'])
            && is_array($fetch_listings_response['listings'])
            && !empty($fetch_listings_response['listings']))
        {
            $first_listing_array = reset($fetch_listings_response['listings']);
            return $first_listing_array;
        }

        return null;
    }

    protected function _getSelfUrlFromListingResponseArray(array $listing_response)
    {
        return isset($listing_response['_links']['self']['href'])
                        ? $listing_response['_links']['self']['href'] : null;
    }

    protected function _getFetchListingsEnpointUrl($magento_sku)
    {
        $rev_url = $this->_reverbSyncHelper->getReverbBaseUrl();
        $escaped_sku = urlencode($magento_sku);
        $params = "state=all&sku=" . $escaped_sku;
        $url = $rev_url . "/api/my/listings?" . $params;
        return $url;
    }

    protected function _validateSku($magento_sku)
    {
        $magento_entity_id = $this->_productRepository->get($magento_sku);
        if (empty($magento_entity_id->getId()))
        {
            $error_message = __(self::ERROR_INVALID_SKU, $magento_sku);
            $this->_reverbLog->logListingsFetchError($error_message);
            throw new \Reverb\ReverbSync\Model\Exception\Data\Listings\Fetch($error_message);
        }

        return $magento_entity_id;
    }

    /**
     * A string description of the API call. It will be used to specify which file to log the curl requests to
     *
     * @return mixed
     */
    public function getApiLogFileSuffix()
    {
        return 'listings_fetch';
    }

    /**
     * This method should return the exception object specific to an API call if one exists. Otherwise, it should return
     *  an object of type Reverb_ReverbSync_Model_Exception_Api
     *
     * @param string $error_message - Exception message
     * @return Reverb_ReverbSync_Model_Exception_Api
     */
    public function getExceptionObject($error_message)
    {
        $exceptionObject = new Reverb_ReverbSync_Model_Exception_Api_Listings_Fetch($error_message);
        return $exceptionObject;
    }

    /**
     * Used to allow for logging errors to specific files. By default, it should call
     *      Mage::getSingleton('reverbSync/log')->logSyncError($error_message);
     *      if no specific log file exists for a specific API call
     *
     * @param $error_message
     */
    public function logError($error_message)
    {
        $this->_reverbLog->logListingsFetchError($error_message);
    }
}
