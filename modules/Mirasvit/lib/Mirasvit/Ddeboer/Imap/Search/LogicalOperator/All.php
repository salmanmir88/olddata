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
 * @package   mirasvit/module-helpdesk
 * @version   1.2.14
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



// namespace Mirasvit_Ddeboer\Imap\Search\LogicalOperator;

// use Mirasvit_Ddeboer\Imap\Search\Condition;

/**
 * Represents an ALL operator. Messages must match all conditions following this
 * operator in order to match the expression.
 */
class Mirasvit_Ddeboer_Imap_Search_LogicalOperator_All extends Mirasvit_Ddeboer_Imap_Search_Condition
{
    /**
     * Returns the keyword that the condition represents.
     *
     * @return string
     */
    public function getKeyword()
    {
        return 'ALL';
    }
}