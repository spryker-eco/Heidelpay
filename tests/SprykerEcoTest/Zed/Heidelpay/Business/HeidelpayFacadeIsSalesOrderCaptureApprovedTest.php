<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEcoTest\Zed\Heidelpay\Business;

use Generated\Shared\Transfer\HeidelpayTransactionLogTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrder;
use SprykerEco\Shared\Heidelpay\HeidelpayConfig;
use SprykerEcoTest\Zed\Heidelpay\Business\DataProviders\PaymentBuilder;

/**
 * @group Functional
 * @group SprykerEcoTest
 * @group Zed
 * @group Heidelpay
 * @group Business
 * @group HeidelpayFacadeIsSalesOrderCaptureApprovedTest
 */
class HeidelpayFacadeIsSalesOrderCaptureApprovedTest extends HeidelpayPaymentTest
{
    /**
     * @return void
     */
    public function testIsSalesOrderCaptureApprovedReturnsTrueWhenTransactionLogContainsAtLeastOneSuccessfulRecord(): void
    {
        // Arrange
        $salesOrder = $this->createOrder();
        $heidelpayFacade = $this->createFacadeWithSuccessfulFactory();

        $heidelpayTransactionLogFailSeedData = [
            HeidelpayTransactionLogTransfer::ID_SALES_ORDER => $salesOrder->getIdSalesOrder(),
            HeidelpayTransactionLogTransfer::TRANSACTION_TYPE => HeidelpayConfig::TRANSACTION_TYPE_CAPTURE,
            HeidelpayTransactionLogTransfer::RESPONSE_CODE => HeidelpayConfig::CAPTURE_TRANSACTION_STATUS_FAILED,
        ];
        $heidelpayTransactionLogSuccessSeedData = [
            HeidelpayTransactionLogTransfer::RESPONSE_CODE => HeidelpayConfig::CAPTURE_TRANSACTION_STATUS_OK,
        ] + $heidelpayTransactionLogFailSeedData;
        $this->tester->haveHeidelpayTransactionLog($heidelpayTransactionLogFailSeedData);
        $this->tester->haveHeidelpayTransactionLog($heidelpayTransactionLogFailSeedData);
        $this->tester->haveHeidelpayTransactionLog($heidelpayTransactionLogSuccessSeedData);

        // Act
        $result = $heidelpayFacade->isSalesOrderCaptureApproved($salesOrder->getIdSalesOrder());

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testIsSalesOrderCaptureApprovedReturnsFalseWhenTransactionLogContainsNoSuccessfulRecords(): void
    {
        // Arrange
        $salesOrder = $this->createOrder();
        $heidelpayFacade = $this->createFacadeWithSuccessfulFactory();

        $this->tester->haveHeidelpayTransactionLog([
            HeidelpayTransactionLogTransfer::ID_SALES_ORDER => $salesOrder->getIdSalesOrder(),
            HeidelpayTransactionLogTransfer::TRANSACTION_TYPE => HeidelpayConfig::TRANSACTION_TYPE_CAPTURE,
            HeidelpayTransactionLogTransfer::RESPONSE_CODE => HeidelpayConfig::CAPTURE_TRANSACTION_STATUS_FAILED,
        ]);

        // Act
        $result = $heidelpayFacade->isSalesOrderCaptureApproved($salesOrder->getIdSalesOrder());

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @return \Orm\Zed\Sales\Persistence\SpySalesOrder
     */
    protected function createOrder(): SpySalesOrder
    {
        return (new PaymentBuilder())->createPayment(PaymentTransfer::HEIDELPAY_CREDIT_CARD_SECURE);
    }
}