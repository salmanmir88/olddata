<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AdminView\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ColorSchema implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->options) {
            $colors = [
                'default' => 'Default',
                'silver' => 'Silver',
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
                'custom' => 'Custom'
            ];

            foreach ($colors as $k => $color) {
                $this->options[] = [
                    'label' => $color,
                    'value' => $k,
                ];
            }
        }

        return $this->options;
    }
}
