<?php
namespace Eextensions\General\Helper;

use Magento\Directory\Helper\Data as DirectryHelper;

class Directory extends DirectryHelper
{
    public function getDefaultCountry($store = null)
    { 
        if(isset($_SESSION["countryid"]) && $_SESSION["countryid"] != ""){ 
            $countryid = $_SESSION["countryid"];
			
			// echo "Helper1 -- ".$countryid;
			
            return $countryid;
       } else {
		   
		   /* echo "Helper2 -- ".$this->scopeConfig->getValue(
				self::XML_PATH_DEFAULT_COUNTRY,
				ScopeInterface::SCOPE_STORE,
				$store
            ); */
			
			return $this->scopeConfig->getValue(
				self::XML_PATH_DEFAULT_COUNTRY,
				ScopeInterface::SCOPE_STORE,
				$store
            );
        } 
    }
}
?>