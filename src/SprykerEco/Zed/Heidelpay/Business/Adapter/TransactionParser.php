<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Heidelpay\Business\Adapter;

use Generated\Shared\Transfer\HeidelpayResponseTransfer;
use Generated\Shared\Transfer\HeidelpayTransactionLogTransfer;
use Heidelpay\PhpPaymentApi\Response;
use SprykerEco\Zed\Heidelpay\Business\Adapter\Mapper\ResponseFromHeidelpayInterface;
use SprykerEco\Zed\Heidelpay\Business\Adapter\Mapper\ResponsePayloadToApiResponseInterface;

class TransactionParser implements TransactionParserInterface
{
    /**
     * @var \SprykerEco\Zed\Heidelpay\Business\Adapter\Mapper\ResponseFromHeidelpayInterface
     */
    protected $responseMapper;

    /**
     * @var \SprykerEco\Zed\Heidelpay\Business\Adapter\Mapper\ResponsePayloadToApiResponseInterface
     */
    protected $payloadToApiResponseMapper;

    /**
     * @param \SprykerEco\Zed\Heidelpay\Business\Adapter\Mapper\ResponseFromHeidelpayInterface $responseMapper
     * @param \SprykerEco\Zed\Heidelpay\Business\Adapter\Mapper\ResponsePayloadToApiResponseInterface $payloadToApiResponseMapper
     */
    public function __construct(
        ResponseFromHeidelpayInterface $responseMapper,
        ResponsePayloadToApiResponseInterface $payloadToApiResponseMapper
    ) {
        $this->responseMapper = $responseMapper;
        $this->payloadToApiResponseMapper = $payloadToApiResponseMapper;
    }

    /**
     * @param \Generated\Shared\Transfer\HeidelpayTransactionLogTransfer $transactionLogTransfer
     *
     * @return \Generated\Shared\Transfer\HeidelpayResponseTransfer
     */
    public function getHeidelpayResponseTransfer(HeidelpayTransactionLogTransfer $transactionLogTransfer): HeidelpayResponseTransfer
    {
        $apiResponseObject = $this->createApiResponse($transactionLogTransfer);

        $responseTransfer = new HeidelpayResponseTransfer();
        $this->responseMapper->map($apiResponseObject, $responseTransfer);

        return $responseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\HeidelpayTransactionLogTransfer $transactionLogTransfer
     *
     * @return \Heidelpay\PhpPaymentApi\Response
     */
    protected function createApiResponse(HeidelpayTransactionLogTransfer $transactionLogTransfer): Response
    {
        $responsePayload = $transactionLogTransfer->getResponsePayload();
        $apiResponseObject = new Response();

        $this->payloadToApiResponseMapper->map($responsePayload, $apiResponseObject);

        return $apiResponseObject;
    }
}
