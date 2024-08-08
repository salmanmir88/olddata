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


// @codingStandardsIgnoreFile
// namespace Mirasvit_Ddeboer\Imap\Exception;

class Mirasvit_Ddeboer_Imap_Exception_MailboxDoesNotExistException extends Mirasvit_Ddeboer_Imap_Exception_Exception
{
    /**
     * Mirasvit_Ddeboer_Imap_Exception_MailboxDoesNotExistException constructor.
     * @param mixed $mailbox
     */
    public function __construct($mailbox)
    {
        parent::__construct('Mailbox '.$mailbox.' does not exist');
    }
}
