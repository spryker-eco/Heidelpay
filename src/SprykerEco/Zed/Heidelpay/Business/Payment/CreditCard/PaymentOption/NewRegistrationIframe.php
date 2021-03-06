<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Heidelpay\Business\Payment\CreditCard\PaymentOption;

use Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer;
use Generated\Shared\Transfer\HeidelpayPaymentOptionTransfer;
use Generated\Shared\Transfer\HeidelpayResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerEco\Shared\Heidelpay\HeidelpayConfig;
use SprykerEco\Zed\Heidelpay\Business\Adapter\Payment\CreditCardPaymentInterface;
use SprykerEco\Zed\Heidelpay\Business\Payment\Request\AdapterRequestFromQuoteBuilderInterface;

class NewRegistrationIframe implements PaymentOptionInterface
{
    /**
     * @var \SprykerEco\Zed\Heidelpay\Business\Payment\Request\AdapterRequestFromQuoteBuilderInterface
     */
    protected $adapterRequestBuilder;

    /**
     * @var \SprykerEco\Zed\Heidelpay\Business\Adapter\Payment\CreditCardPaymentInterface
     */
    protected $creditCardPayment;

    /**
     * @param \SprykerEco\Zed\Heidelpay\Business\Payment\Request\AdapterRequestFromQuoteBuilderInterface $adapterRequestBuilder
     * @param \SprykerEco\Zed\Heidelpay\Business\Adapter\Payment\CreditCardPaymentInterface $creditCardPayment
     */
    public function __construct(
        AdapterRequestFromQuoteBuilderInterface $adapterRequestBuilder,
        CreditCardPaymentInterface $creditCardPayment
    ) {
        $this->adapterRequestBuilder = $adapterRequestBuilder;
        $this->creditCardPayment = $creditCardPayment;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
     *
     * @return void
     */
    public function hydrateToPaymentOptions(
        QuoteTransfer $quoteTransfer,
        HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
    ): void {
        $registrationResponseTransfer = $this->registerQuote($quoteTransfer);
        $this->mapResponseToPaymentOptions($paymentOptionsTransfer, $registrationResponseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    public function isOptionAvailableForQuote(QuoteTransfer $quoteTransfer): bool
    {
        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\HeidelpayResponseTransfer
     */
    protected function registerQuote(QuoteTransfer $quoteTransfer): HeidelpayResponseTransfer
    {
        $registrationRequestTransfer = $this->adapterRequestBuilder->buildCreditCardRegistrationRequest($quoteTransfer);
        $registrationResponseTransfer = $this->creditCardPayment->register($registrationRequestTransfer);

        return $registrationResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
     * @param \Generated\Shared\Transfer\HeidelpayResponseTransfer $registrationResponseTransfer
     *
     * @return void
     */
    protected function mapResponseToPaymentOptions(
        HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer,
        HeidelpayResponseTransfer $registrationResponseTransfer
    ): void {
        if ($registrationResponseTransfer->getIsError()) {
            return;
        }

        $this->addNewRegistrationAsPaymentOption($paymentOptionsTransfer);

        $paymentOptionsTransfer
            ->setPaymentFrameUrl($registrationResponseTransfer->getPaymentFormUrl());
    }

    /**
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
     *
     * @return void
     */
    protected function addNewRegistrationAsPaymentOption(HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer): void
    {
        $optionsList = $paymentOptionsTransfer->getOptionsList();

        $optionsList[] = (new HeidelpayPaymentOptionTransfer())
            ->setCode(HeidelpayConfig::PAYMENT_OPTION_NEW_REGISTRATION);

        $paymentOptionsTransfer->setOptionsList($optionsList);
    }
}
