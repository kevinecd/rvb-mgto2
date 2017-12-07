<?php
namespace Reverb\Base\Helper;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const EXTRACT_DOMAIN_FROM_URL_REGEX = '#.*\://?([^\/]+)#';

    public function isAdminLoggedIn()
    {
        $admin_user_id = Mage::helper('adminhtml')->getCurrentUserId();
        return (!empty($admin_user_id));
    }

    public function extractDomainFromUrl($url)
    {
        $matches = array();
        $result = preg_match(self::EXTRACT_DOMAIN_FROM_URL_REGEX, $url, $matches);
        if ($result && isset($matches[1]) && !empty($matches[1]))
        {
            return $matches[1];
        }

        return $url;
    }
} 