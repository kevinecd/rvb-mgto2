<?php
namespace Reverb\ReverbSync\Model\Resource\Task\Shipment;
class Tracking extends \Reverb\ReverbSync\Model\Resource\Task\Unique
{
    const ERROR_ADDING_PRODUCT_DATA = 'An error occurred while adding product data to a Reverb Shipment Tracking Sync queue task object for shipment track with tracking number %s: %s';

    const SHIPMENT_TRACKING_TASK_OBJECT = '\Reverb\ReverbSync\Model\Sync\Shipment\Tracking';
    const SHIPMENT_TRACKING_TASK_METHOD = 'transmitTrackingDataToReverb';

    protected $_tracking_data_values_to_serialize = array(
        'carrier_code' => 'carrier_code',
        'title' => 'title',
        'number' => 'number',
        'track_number' => 'track_number',
        'parent_id' => 'parent_id',
        'order_id' => 'order_id',
        'store_id' => 'store_id',
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
        'entity_id' => 'entity_id',
    );

    protected $_reverbShipmentHelper = null;

    /**
     * The unique key for the shipment tracking object syncs will be a concatenation of the following:
     *
     *  reverb_order_id
     *  shipping carrier code
     *  tracking_number
     *
     * @param \Magento\Sales\Model\Order\Shipment\Track $shipmentTrackingObject
     * @return int
     */
    public function queueShipmentTrackingTransmission(\Magento\Sales\Model\Order\Shipment\Track $shipmentTrackingObject, $unique_id_key, $reverb_order_id, $resourceOrderSync)
    {
        /*$unique_id_key = $this->_getReverbShipmentHelper()->getTrackingSyncQueueTaskUniqueId($shipmentTrackingObject);
        $reverb_order_id = $this->_getReverbShipmentHelper()
                                    ->getReverbOrderIdForMagentoShipmentTrackingObject($shipmentTrackingObject);*/

        $insert_data_array = $this->_getUniqueInsertDataArrayTemplate(self::SHIPMENT_TRACKING_TASK_OBJECT,
                                                                        self::SHIPMENT_TRACKING_TASK_METHOD, $unique_id_key);

        $tracking_data = $shipmentTrackingObject->getData();
        $tracking_data_to_serialize = array_intersect_key($tracking_data, $this->_tracking_data_values_to_serialize);
        $tracking_data_to_serialize['reverb_order_id'] = $reverb_order_id;

        try
        {
            $magentoShipment = $shipmentTrackingObject->getShipment();
            if (is_object($magentoShipment))
            {
                $magentoOrder = $magentoShipment->getOrder();
                if (is_object($magentoOrder))
                {
                    $magentoProduct = $resourceOrderSync->getReverbOrderItemByOrder($magentoOrder);
                    $tracking_data_to_serialize['sku'] = $magentoProduct->getSku();
                    $tracking_data_to_serialize['name'] = $magentoProduct->getName();
                }
            }
        }
        catch(\Exception $e)
        {
            // Do not stop execution, but log the exception
            $error_message = __(sprintf(self::ERROR_ADDING_PRODUCT_DATA, $shipmentTrackingObject->getTrackNumber(), $e->getMessage()));
            $this->_getReverbShipmentHelper()->logError($error_message);
        }

        $serialized_arguments_object = serialize($tracking_data_to_serialize);
        $insert_data_array['serialized_arguments_object'] = $serialized_arguments_object;

        $number_of_inserted_rows = $this->getConnection()->insert($this->getMainTable(), $insert_data_array);

        return $number_of_inserted_rows;
    }

    public function getQueueTaskIdForShipmentTrackingObject(\Magento\Sales\Model\Order\Shipment\Track $shipmentTrackingObject, $unique_id_key)
    {
        /*$unique_id_key = $this->_getReverbShipmentHelper()->getTrackingSyncQueueTaskUniqueId($shipmentTrackingObject);*/
        $task_primary_key = $this->getPrimaryKeyByUniqueId($unique_id_key);

        return $task_primary_key;
    }

    /**
     * @return string
     */
    public function getTaskCode()
    {
        return \Reverb\ReverbSync\Model\Sync\Shipment\Tracking::JOB_CODE;
    }

    /**
     * @return Reverb_ReverbSync_Helper_Shipment_Data
     */
    protected function _getReverbShipmentHelper()
    {
        return $this->_reverbShipmentHelper;
    }
}
