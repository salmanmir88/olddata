<?php
/**
 * @category  Apptrian
 * @package   Apptrian_FacebookPixel
 * @author    Apptrian
 * @copyright Copyright (c) Apptrian (http://www.apptrian.com)
 * @license   http://www.apptrian.com/license Proprietary Software License EULA
 */

namespace Apptrian\FacebookPixel\Block\Adminhtml;

use Magento\Framework\Data\Form\Element\AbstractElement;

class About extends \Magento\Backend\Block\AbstractBlock implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var \Apptrian\FacebookPixel\Helper\Data
     */
    public $helper;
    
    /**
     * Constructor
     *
     * @param \Apptrian\FacebookPixel\Helper\Data $helper
     */
    public function __construct(\Apptrian\FacebookPixel\Helper\Data $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * Retrieve element HTML markup.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element  = null;
        $version  = $this->helper->getExtensionVersion();
        $logopath = 'https://www.apptrian.com/media/apptrian.gif';
        $html     = <<<HTML
<div style="background: url('$logopath') no-repeat scroll 15px 15px #f8f8f8; 
border:1px solid #ccc; min-height:100px; margin:5px 0; 
padding:15px 15px 15px 140px;">
<p>
<strong>Apptrian Facebook Pixel and Conversions API Extension v$version</strong><br /><br />
Adds Facebook Pixel and Facebook Conversions API (Facebook Server-Side API) with standard events and 
Dynamic Ads code on appropriate pages. Supports Advanced Matching 
(if the customer is logged in) and has the ability to add custom parameters. 
Passes W3C validation. Easy to install and use.
</p>
</div>
HTML;
        return $html;
    }
}
