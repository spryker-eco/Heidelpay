<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Heidelpay\Business\Processor\Notification;

use Generated\Shared\Transfer\HeidelpayNotificationTransfer;

interface HeidelpayNotificationProcessorInterface
{
    /**
     * @param \Generated\Shared\Transfer\HeidelpayNotificationTransfer $notificationTransfer
     *
     * @return \Generated\Shared\Transfer\HeidelpayNotificationTransfer
     */
    public function processNotification(HeidelpayNotificationTransfer $notificationTransfer): HeidelpayNotificationTransfer;
}
