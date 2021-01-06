<?php

/**
 * MIT License
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace SprykerEco\Zed\Heidelpay\Business\Payment\CreditCard\PaymentOption;

use Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer;
use Generated\Shared\Transfer\HeidelpayCreditCardRegistrationTransfer;
use Generated\Shared\Transfer\HeidelpayPaymentOptionTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerEco\Shared\Heidelpay\HeidelpayConfig;
use SprykerEco\Zed\Heidelpay\Business\Payment\CreditCard\Registration\RegistrationReaderInterface;

class LastSuccessfulRegistration implements PaymentOptionInterface
{
    /**
     * @var \SprykerEco\Zed\Heidelpay\Business\Payment\CreditCard\Registration\RegistrationReaderInterface
     */
    protected $registrationReader;

    /**
     * @param \SprykerEco\Zed\Heidelpay\Business\Payment\CreditCard\Registration\RegistrationReaderInterface $registrationManager
     */
    public function __construct(RegistrationReaderInterface $registrationManager)
    {
        $this->registrationReader = $registrationManager;
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
        $lastSuccessfulRegistrationTransfer = $this->getLastSuccessfulRegistrationForQuote($quoteTransfer);

        if ($lastSuccessfulRegistrationTransfer->getIdCreditCardRegistration() !== null) {
            $this->mapRegistrationToPaymentOptions($lastSuccessfulRegistrationTransfer, $paymentOptionsTransfer);
        }
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    public function isOptionAvailableForQuote(QuoteTransfer $quoteTransfer): bool
    {
        return $this->isRegisteredShippingAddressUsed($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\HeidelpayCreditCardRegistrationTransfer
     */
    protected function getLastSuccessfulRegistrationForQuote(QuoteTransfer $quoteTransfer): HeidelpayCreditCardRegistrationTransfer
    {
        if ($this->hasQuoteValidLastRegistration($quoteTransfer)) {
            return $this->getLastSuccessfulRegistrationFromQuote($quoteTransfer);
        }

        return $this->registrationReader->getLastSuccessfulRegistrationForQuote($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardRegistrationTransfer $creditCardRegistrationTransfer
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
     *
     * @return void
     */
    protected function mapRegistrationToPaymentOptions(
        HeidelpayCreditCardRegistrationTransfer $creditCardRegistrationTransfer,
        HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
    ): void {
        $this->addLastSuccessfulRegistrationAsPaymentOption($paymentOptionsTransfer);

        $paymentOptionsTransfer
            ->setLastSuccessfulRegistration($creditCardRegistrationTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
     *
     * @return void
     */
    protected function addLastSuccessfulRegistrationAsPaymentOption(
        HeidelpayCreditCardPaymentOptionsTransfer $paymentOptionsTransfer
    ): void {
        $optionsList = $paymentOptionsTransfer->getOptionsList();
        $optionsList[] = (new HeidelpayPaymentOptionTransfer())
            ->setCode(HeidelpayConfig::PAYMENT_OPTION_EXISTING_REGISTRATION);

        $paymentOptionsTransfer->setOptionsList($optionsList);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    protected function isRegisteredShippingAddressUsed(QuoteTransfer $quoteTransfer): bool
    {
        return ($quoteTransfer->getShippingAddress() !== null) &&
            ($quoteTransfer->getShippingAddress()->getIdCustomerAddress() !== null) &&
            ($quoteTransfer->getShippingAddress()->getIdCustomerAddress() !== '');
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return bool
     */
    protected function hasQuoteValidLastRegistration(QuoteTransfer $quoteTransfer): bool
    {
        $lastRegistrationTransfer = $this->getLastSuccessfulRegistrationFromQuote($quoteTransfer);

        if ($lastRegistrationTransfer === null) {
            return false;
        }

        return $this->isQuoteShippingAddressChanged($quoteTransfer, $lastRegistrationTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\HeidelpayCreditCardRegistrationTransfer|null
     */
    protected function getLastSuccessfulRegistrationFromQuote(QuoteTransfer $quoteTransfer): ?HeidelpayCreditCardRegistrationTransfer
    {
        $lastRegistration = $quoteTransfer
            ->getPayment()
            ->getHeidelpayCreditCardSecure()
            ->getPaymentOptions()
            ->getLastSuccessfulRegistration();

        return $lastRegistration;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\HeidelpayCreditCardRegistrationTransfer $lastRegistrationTransfer
     *
     * @return bool
     */
    protected function isQuoteShippingAddressChanged(
        QuoteTransfer $quoteTransfer,
        HeidelpayCreditCardRegistrationTransfer $lastRegistrationTransfer
    ): bool {
        $customerShippingAddressId = $quoteTransfer->getShippingAddress()->getIdCustomerAddress();
        $lastRegistrationAddressId = $lastRegistrationTransfer->getIdCustomerAddress();

        return $customerShippingAddressId === $lastRegistrationAddressId;
    }
}
