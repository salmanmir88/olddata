<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AdminView\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Extension enabled config path
     */
    const XML_PATH_EXTENSION_ENABLED = 'mfadminview/general/enabled';

    /**
     * Main logo config path
     */
    const XML_PATH_MAIN_LOGO = 'mfadminview/logos/main_logo';

    /**
     * Menu logo config path
     */
    const XML_PATH_MENU_LOGO = 'mfadminview/logos/menu_logo';

    /** Color Schema */
    const XML_PATH_SELECT_COLOR_SCHEMA = 'mfadminview/color_schema/theme';
    const XML_PATH_MENU_BG = 'mfadminview/color_schema/main_menu/bg';
    const XML_PATH_MENU_BG_HOVER = 'mfadminview/color_schema/main_menu/bg_on_hover';
    const XML_PATH_MENU_TEXT = 'mfadminview/color_schema/main_menu/text';
    const XML_PATH_MENU_TEXT_HOVER = 'mfadminview/color_schema/main_menu/text_on_hover';
    const XML_PATH_PRIMARY_BUTTON_BG = 'mfadminview/color_schema/button/primary_button_bg';
    const XML_PATH_PRIMARY_BUTTON_BG_HOVER = 'mfadminview/color_schema/button/primary_button_bg_on_hover';
    const XML_PATH_BUTTON_BG = 'mfadminview/color_schema/button/button_bg';
    const XML_PATH_BUTTON_BG_HOVER = 'mfadminview/color_schema/button/button_on_hover';
    const XML_PATH_GRID_BG = 'mfadminview/color_schema/grid/bg';
    const XML_PATH_GRID_BG_HOVER = 'mfadminview/color_schema/grid/bg_on_hover';

    /**
     * Footer config
     */
    const XML_PATH_FOOTER_COPYRIGHT = 'mfadminview/footer/copyright';
    const XML_PATH_FOOTER_VERSION = 'mfadminview/footer/version';
    const XML_PATH_FOOTER_BUTTONS = 'mfadminview/footer/buttons';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve true if blog module is enabled
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(self::XML_PATH_EXTENSION_ENABLED, $storeId);
    }

    /**
     * Retrieve main logo path
     * @param null $storeId
     * @return string
     */
    public function getMainLogo($storeId = null)
    {
        return (string)$this->getConfig(self::XML_PATH_MAIN_LOGO, $storeId);
    }

    /**
     * Retrieve menu logo path
     * @param null $storeId
     * @return string
     */
    public function getMenuLogo($storeId = null)
    {
        return (string)$this->getConfig(self::XML_PATH_MENU_LOGO, $storeId);
    }

    /**
     * @param $storeId
     * @return string|array
     */
    private function getTheme($storeId)
    {
        $theme = $this->getConfig(self::XML_PATH_SELECT_COLOR_SCHEMA, $storeId);

        switch ($theme) {
            case 'silver':
                return $this->getSilverTheme();
            case 'red':
                return $this->getRedTheme();
            case 'blue':
                return $this->getBlueTheme();
            case 'green':
                return $this->getGreenTheme();
            case 'custom':
                return $this->getCustomTheme($storeId);
            default:
                return '';
        }
    }

    /**
     * Retrieve color schema
     * @param $field
     * @param null $storeId
     * @return string
     */
    public function getColorSchema($field, $storeId = null)
    {
        if (!is_array($this->getTheme($storeId))) {
            return '';
        }

        $color = array_filter($this->getTheme($storeId), function ($k) use ($field) {
            return $k == $field;
        }, ARRAY_FILTER_USE_KEY);

        return '#' . array_values($color)[0];
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getAdminFooterConfig($storeId = null)
    {
        return [
            'copyright' => $this->getConfig(self::XML_PATH_FOOTER_COPYRIGHT, $storeId),
            'version' => $this->getConfig(self::XML_PATH_FOOTER_VERSION, $storeId),
            'buttons' => $this->getConfig(self::XML_PATH_FOOTER_BUTTONS, $storeId),
        ];
    }

    /**
     * Retrieve store config value
     * @param $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array
     */
    private function getSilverTheme()
    {
        return [
            'main_menu_bg' => 'f2f2f2',
            'main_menu_bg_on_hover' => 'dedede',
            'main_menu_text' => '303030',
            'main_menu_text_on_hover' => '000000',
            'primary_button_bg' => 'c72424',
            'primary_button_bg_on_hover' => '8f1b1b',
            'button_bg' => '4f4e4e',
            'button_on_hover' => '3b3b3b',
            'grid_bg' => 'c72424',
            'grid_bg_on_hover' => '8f1b1b'
        ];
    }

    /**
     * @return array
     */
    private function getRedTheme()
    {
        return [
            'main_menu_bg' => '330000',
            'main_menu_bg_on_hover' => 'b30000',
            'main_menu_text' => 'ff9999',
            'main_menu_text_on_hover' => 'fff',
            'primary_button_bg' => 'b30000',
            'primary_button_bg_on_hover' => '800000',
            'button_bg' => 'ff6600',
            'button_on_hover' => 'b34700',
            'grid_bg' => 'ff9999',
            'grid_bg_on_hover' => 'b30000'
        ];
    }

    /**
     * @return array
     */
    private function getBlueTheme()
    {
        return [
                'main_menu_bg' => '333B4E',
                'main_menu_bg_on_hover' => '1375c0',
                'main_menu_text' => '828a9f',
                'main_menu_text_on_hover' => 'fff',
                'primary_button_bg' => '1375c0',
                'primary_button_bg_on_hover' => '0e558b',
                'button_bg' => '676e87',
                'button_on_hover' => '434756',
                'grid_bg' => '676e87',
                'grid_bg_on_hover' => '434756'
        ];
    }

    /**
     * @return array
     */
    private function getGreenTheme()
    {
        return [
            'main_menu_bg' => '12240f',
            'main_menu_bg_on_hover' => '448637',
            'main_menu_text' => '94aa8c',
            'main_menu_text_on_hover' => 'fff',
            'primary_button_bg' => '448637',
            'primary_button_bg_on_hover' => '376d2c',
            'button_bg' => '94aa8c',
            'button_on_hover' => '5f7557',
            'grid_bg' => '94aa8c',
            'grid_bg_on_hover' => '5f7557'
        ];
    }

    /**
     * @var $storeId
     * @return array
     */
    private function getCustomTheme($storeId)
    {
        return [
            'main_menu_bg' => $this->getConfig(self::XML_PATH_MENU_BG, $storeId),
            'main_menu_bg_on_hover' => $this->getConfig(self::XML_PATH_MENU_BG_HOVER, $storeId),
            'main_menu_text' => $this->getConfig(self::XML_PATH_MENU_TEXT, $storeId),
            'main_menu_text_on_hover' => $this->getConfig(self::XML_PATH_MENU_TEXT_HOVER, $storeId),
            'primary_button_bg' => $this->getConfig(self::XML_PATH_PRIMARY_BUTTON_BG, $storeId),
            'primary_button_bg_on_hover' => $this->getConfig(self::XML_PATH_PRIMARY_BUTTON_BG_HOVER, $storeId),
            'button_bg' => $this->getConfig(self::XML_PATH_BUTTON_BG, $storeId),
            'button_on_hover' => $this->getConfig(self::XML_PATH_BUTTON_BG_HOVER, $storeId),
            'grid_bg' => $this->getConfig(self::XML_PATH_GRID_BG, $storeId),
            'grid_bg_on_hover' => $this->getConfig(self::XML_PATH_GRID_BG_HOVER, $storeId)
        ];
    }
}
