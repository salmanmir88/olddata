<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.38
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Validator;


class RequiredValueValidator implements ValidatorInterface
{
    const CODE = 'required';
    const NAME = 'Required Value';

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * #1 $value = 'str'
     * // !empty($value) => true
     * -> return true;
     *
     * #2 $value = ''
     * // !empty($value) => false
     * // is_numeric($value) => false
     * -> return false
     *
     * #3 $value = 0
     * // !empty($value) => false
     * // is_numeric($value) => true
     * -> return true
     *
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        return !empty($value) || is_numeric($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getMessage($isHtml = false)
    {
        $message = __('Missing required attribute');

        if ($isHtml) {
            $message = '<span class="grid-severity-major"><span>'.$message.'</span></span>';
        }

        return $message;
    }

    /**
     * {@inheritDoc}
     */
    public function getHint($attribute = '')
    {
        return __("Products without this attribute won't be accepted by the target shopping channel. "
            . "To fix this error, open invalid products and fill in a value for this attribute "
            . "or change an attribute/pattern used for this field in the product feed itself."
        );
    }
}
