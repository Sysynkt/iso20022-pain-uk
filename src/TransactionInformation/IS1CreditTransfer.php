<?php

namespace Academe\Pain001\TransactionInformation;

use DOMDocument;
use InvalidArgumentException;
use Academe\Pain001\Money;
use Academe\Pain001\PaymentInformation\PaymentInformation;
use Academe\Pain001\Account\PostalAccount;
use Academe\Pain001\PostalAddressInterface;

/**
 * IS1CreditTransfer contains all the information about a IS 1-stage (type 2.1) transaction.
 */
class IS1CreditTransfer extends CreditTransfer
{
    /**
     * @var \Academe\Pain001\Account\PostalAccount
     */
    protected $creditorAccount;

    /**
     * {@inheritdoc}
     *
     * @param \Academe\Pain001\Account\PostalAccount $creditorAccount Postal account of the creditor
     *
     * @throws \InvalidArgumentException When the amount is not in EUR or CHF.
     */
    public function __construct($instructionId, $endToEndId, Money\Money $amount, $creditorName, PostalAddressInterface $creditorAddress, PostalAccount $creditorAccount)
    {
        if (!$amount instanceof Money\EUR && !$amount instanceof Money\CHF) {
            throw new InvalidArgumentException(sprintf(
                'The amount must be an instance of Academe\Pain001\Money\EUR or Academe\Pain001\Money\CHF (instance of %s given).',
                get_class($amount)
            ));
        }

        parent::__construct($instructionId, $endToEndId, $amount, $creditorName, $creditorAddress);

        $this->creditorAccount = $creditorAccount;
        $this->localInstrument = 'CH02';
    }

    /**
     * {@inheritdoc}
     */
    public function asDom(DOMDocument $doc, PaymentInformation $paymentInformation)
    {
        $root = $this->buildHeader($doc, $paymentInformation);

        $root->appendChild($this->buildCreditor($doc));

        $creditorAccount = $doc->createElement('CdtrAcct');
        $creditorAccount->appendChild($this->creditorAccount->asDom($doc));
        $root->appendChild($creditorAccount);

        $this->appendPurpose($doc, $root);

        $this->appendRemittanceInformation($doc, $root);

        return $root;
    }
}
