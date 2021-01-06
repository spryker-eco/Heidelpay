<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Heidelpay\Communication\Plugin\Checkout\Oms\Condition;

use Orm\Zed\Heidelpay\Persistence\SpyPaymentHeidelpayTransactionLog;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Oms\Dependency\Plugin\Condition\ConditionInterface;
use SprykerEco\Shared\Heidelpay\HeidelpayConfig;

/**
 * @method \SprykerEco\Zed\Heidelpay\Business\HeidelpayFacadeInterface getFacade()
 * @method \SprykerEco\Zed\Heidelpay\Persistence\HeidelpayQueryContainerInterface getQueryContainer()
 * @method \SprykerEco\Zed\Heidelpay\Communication\HeidelpayCommunicationFactory getFactory()
 * @method \SprykerEco\Zed\Heidelpay\HeidelpayConfig getConfig()
 */
class IsFinalizeCompletedPlugin extends AbstractPlugin implements ConditionInterface
{
    /**
     * {@inheritDoc}
     * - Checks if Finalize transaction was successful.
     *
     * @api
     *
     * @param \Orm\Zed\Sales\Persistence\SpySalesOrderItem $orderItem
     *
     * @return bool
     */
    public function check(SpySalesOrderItem $orderItem)
    {
        return $this->isFinalizeSuccessful($orderItem->getFkSalesOrder());
    }

    /**
     * @param int $idSalesOrder
     *
     * @return bool
     */
    protected function isFinalizeSuccessful($idSalesOrder)
    {
        $finalizeTransactionLog = $this->getFinalizeTransactionLogEntry($idSalesOrder);
        if ($finalizeTransactionLog === null) {
            return false;
        }

        return $this->isTransactionSuccessful($finalizeTransactionLog);
    }

    /**
     * @param int $idSalesOrder
     *
     * @return \Orm\Zed\Heidelpay\Persistence\SpyPaymentHeidelpayTransactionLog|null
     */
    protected function getFinalizeTransactionLogEntry($idSalesOrder)
    {
        $transactionLogQuery = $this->getQueryContainer()->queryFinalizeTransactionLog($idSalesOrder);

        return $transactionLogQuery->findOne();
    }

    /**
     * @param \Orm\Zed\Heidelpay\Persistence\SpyPaymentHeidelpayTransactionLog $finalizeTransactionLog
     *
     * @return bool
     */
    protected function isTransactionSuccessful(SpyPaymentHeidelpayTransactionLog $finalizeTransactionLog)
    {
        return $finalizeTransactionLog->getResponseCode() === HeidelpayConfig::FINALIZE_TRANSACTION_STATUS_OK;
    }
}
