<?php

namespace Tiime\FacturX;

use Alcohol\ISO4217;
use Tiime\FacturX\BusinessTermsGroup\AdditionalSupportingDocuments;
use Tiime\FacturX\BusinessTermsGroup\Buyer;
use Tiime\FacturX\BusinessTermsGroup\DeliverToAddress;
use Tiime\FacturX\BusinessTermsGroup\DeliveryInformation;
use Tiime\FacturX\BusinessTermsGroup\DocumentLevelAllowances;
use Tiime\FacturX\BusinessTermsGroup\DocumentLevelCharges;
use Tiime\FacturX\BusinessTermsGroup\DocumentTotals;
use Tiime\FacturX\BusinessTermsGroup\InvoiceLine;
use Tiime\FacturX\BusinessTermsGroup\InvoiceNote;
use Tiime\FacturX\BusinessTermsGroup\InvoiceTypeCode;
use Tiime\FacturX\BusinessTermsGroup\Payee;
use Tiime\FacturX\BusinessTermsGroup\PaymentInstructions;
use Tiime\FacturX\BusinessTermsGroup\PrecedingInvoiceReference;
use Tiime\FacturX\BusinessTermsGroup\ProcessControl;
use Tiime\FacturX\BusinessTermsGroup\Seller;
use Tiime\FacturX\BusinessTermsGroup\SellerTaxRepresentativeParty;
use Tiime\FacturX\BusinessTermsGroup\VatBreakdown;

class Invoice
{
    /**
     * BT-1
     * A unique identification of the Invoice.
     */
    private string $number;

    /**
     * BT-2
     * The date when the Invoice was issued.
     */
    private \DateTimeInterface $issueDate;

    /**
     * BT-3
     * A code specifying the functional type of the Invoice.
     *
     */
    private InvoiceTypeCode $typeCode;

    /**
     * BT-5
     * The currency in which all Invoice amounts are given, except for the Total VAT amount in accounting currency.
     */
    private string $currencyCode;

    /**
     * BT-6
     * The currency used for VAT accounting and reporting purposes as accepted or required in the country of the Seller.
     *
     * @var
     */
    private $vatAccountingCurrencyCode;

    /**
     * BT-7
     * The date when the VAT becomes accountable for the Seller and for the Buyer in so far as
     * that date can be determined and differs from the date of issue of the invoice, according to the VAT directive.
     *
     * @var \DateTimeInterface|null
     */
    private $valueAddedTaxPointDate;

    /**
     * BT-8
     * The code of the date when the VAT becomes accountable for the Seller and for the Buyer.
     *
     * @var
     */
    private $valueAddedTaxPointDateCode;

    /**
     * BT-9
     * The date when the payment is due.
     *
     * @var \DateTimeInterface|null
     */
    private $paymentDueDate;

    /**
     * BT-10
     * An identifier assigned by the Buyer used for internal routing purposes.
     *
     * Identifiant assigné par l'Acheteur et destiné au routage de la facture en interne.
     */
    private ?string $buyerReference;

    /**
     * BT-11
     * The identification of the project the invoice refers to.
     *
     * @var
     */
    private $projectReference;

    /**
     * BT-12
     * The identification of a contract.
     *
     * @var
     */
    private $contractReference;

    /**
     * BT-13
     * An identifier of a referenced purchase order, issued by the Buyer.
     *
     * @var
     */
    private $purchaseOrderReference;

    /**
     * BT-14
     * An identifier of a referenced sales order, issued by the Seller.
     *
     * @var
     */
    private $salesOrderReference;

    /**
     * BT-15
     * An identifier of a referenced receiving advice.
     *
     * @var
     */
    private $receivingAdviceReference;

    /**
     * BT-16
     * An identifier of a referenced despatch advice.
     *
     * @var
     */
    private $despatchAdviceReference;

    /**
     * BT-17
     * The identification of the call for tender or lot the invoice relates to.
     *
     * @var
     */
    private $tenderOrLotReference;

    /** @todo BT18 */

    /**
     * BT-19
     * A textual value that specifies where to book the relevant data into the Buyer's financial accounts.
     *
     * @var string|null
     */
    private $buyerAccountingReference;

    /**
     * BT-20
     * A textual description of the payment terms that apply to
     * the amount due for payment (Including description of possible penalties).
     *
     * @var string|null
     */
    private $paymentTerms;

    /**
     * @var InvoiceNote[]
     */
    private $invoiceNote;

    /**
     * @var ProcessControl
     */
    private $processControl;

    /**
     * @var PrecedingInvoiceReference[]
     */
    private $precedingInvoiceReference;

    private Seller $seller;

    private Buyer $buyer;

    /**
     * @var Payee|null
     */
    private $payee;

    /**
     * @var SellerTaxRepresentativeParty|null
     */
    private $sellerTaxRepresentativeParty;

    private ?DeliveryInformation $deliveryInformation;

    private ?PaymentInstructions $paymentInstructions;

    /**
     * @var DocumentLevelAllowances[]
     */
    private $documentLevelAllowances;

    /**
     * @var DocumentLevelCharges[]
     */
    private $documentLevelCharges;

    /**
     * @var DocumentTotals
     */
    private $documentTotals;

    /**
     * @var VatBreakdown[]
     */
    private $vatBreakdowns;

    /**
     * @var AdditionalSupportingDocuments[]
     */
    private $additionalSupportingDocuments;

    /**
     * @var InvoiceLine[]
     */
    private $invoiceLines;

    public function __construct(
        string $number,
        \DateTimeInterface $issueDate,
        \DateTimeInterface $paymentDueDate,
        InvoiceTypeCode $typeCode,
        string $currencyCode,
        ProcessControl $processControl,
        Seller $seller,
        Buyer $buyer,
        DocumentTotals $documentTotals,
        array $vatBreakdowns,
        array $invoiceLines
    ) {
        $this->vatBreakdowns = [];
        foreach ($vatBreakdowns as $vatBreakdown) {
            if ($vatBreakdown instanceof VatBreakdown) {
                $this->vatBreakdowns[] = $vatBreakdown;
            }
        }

        if (empty($this->vatBreakdowns)) {
            throw new \Exception('@todo');
        }

        $this->invoiceLines = [];
        foreach ($invoiceLines as $invoiceLine) {
            if ($invoiceLine instanceof InvoiceLine) {
                $this->invoiceLines[] = $invoiceLine;
            }
        }

        if (empty($this->invoiceLines)) {
            throw new \Exception('@todo');
        }

        (new ISO4217())->getByCode($currencyCode);

        $this->number = $number;
        $this->issueDate = $issueDate;
        $this->paymentDueDate = $paymentDueDate;
        $this->typeCode = $typeCode;
        $this->invoiceNote = [];
        $this->currencyCode = $currencyCode;
        $this->processControl = $processControl;
        $this->seller = $seller;
        $this->buyer = $buyer;
        $this->documentTotals = $documentTotals;

        $this->buyerReference = null;
        $this->deliveryInformation = null;
        $this->paymentInstructions = null;
    }

    public function getXML(): \DOMDocument
    {
        $invoiceXML = new \DOMDocument('1.0', 'UTF-8');

        $crossIndustryInvoice = $invoiceXML->createElement('rsm:CrossIndustryInvoice');
        $crossIndustryInvoice->setAttribute(
            'xmlns:qdt',
            'urn:un:unece:uncefact:data:standard:QualifiedDataType:100'
        );
        $crossIndustryInvoice->setAttribute(
            'xmlns:ram',
            'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100'
        );
        $crossIndustryInvoice->setAttribute(
            'xmlns:rsm',
            'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100'
        );
        $crossIndustryInvoice->setAttribute(
            'xmlns:udt',
            'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100'
        );
        $crossIndustryInvoice->setAttribute(
            'xmlns:xsi',
            'http://www.w3.org/2001/XMLSchema-instance'
        );

        $root = $invoiceXML->appendChild($crossIndustryInvoice);

        $root->appendChild($invoiceXML->createElement('rsm:ExchangedDocumentContext'));
        $root->appendChild($invoiceXML->createElement('rsm:ExchangedDocument'));
        $supplyChainTradeTransaction = $invoiceXML->createElement('rsm:SupplyChainTradeTransaction');
        $root->appendChild($supplyChainTradeTransaction);

        $this->processControl->hydrateXmlDocument($invoiceXML);

        $this->appendToExchangedDocument($invoiceXML, $invoiceXML->createElement('ram:ID', $this->number));
        $this->appendToExchangedDocument(
            $invoiceXML,
            $invoiceXML->createElement('ram:TypeCode', $this->typeCode->value)
        );
        $issueDate = $invoiceXML->createElement('ram:IssueDateTime');
        $issueDateString = $invoiceXML->createElement('udt:DateTimeString', $this->issueDate->format('Ymd'));
        $issueDateString->setAttribute('format', '102');
        $issueDate->appendChild($issueDateString);
        $this->appendToExchangedDocument($invoiceXML, $issueDate);

        if (ProcessControl::MINIMUM !== $this->processControl->getSpecificationIdentifier()) {
            foreach ($this->invoiceNote as $note) {
                $note->hydrateXmlDocument($invoiceXML);
            }
        }

        if (ProcessControl::MINIMUM !== $this->processControl->getSpecificationIdentifier()) {
            foreach ($this->invoiceLines as $line) {
                $line->hydrateXmlDocument($invoiceXML);
            }
        }

        $applicableHeaderTradeAgreement = $invoiceXML->createElement('ram:ApplicableHeaderTradeAgreement');

        if (null !== $this->buyerReference) {
            $applicableHeaderTradeAgreement->appendChild(
                $invoiceXML->createElement('ram:BuyerReference', $this->buyerReference)
            );
        }

        $supplyChainTradeTransaction->appendChild($applicableHeaderTradeAgreement);

        $this->seller->hydrateXmlDocument($invoiceXML);
        $this->buyer->hydrateXmlDocument($invoiceXML);

        $supplyChainTradeTransaction->appendChild($invoiceXML->createElement('ram:ApplicableHeaderTradeDelivery'));

        if ($this->deliveryInformation instanceof DeliveryInformation) {
            $this->deliveryInformation->hydrateXmlDocument($invoiceXML);
        }

        $applicableHeaderTradeSettlement = $invoiceXML->createElement('ram:ApplicableHeaderTradeSettlement');
        $applicableHeaderTradeSettlement->appendChild(
            $invoiceXML->createElement('ram:InvoiceCurrencyCode', $this->currencyCode)
        );

        if ($this->paymentInstructions instanceof PaymentInstructions) {
            $this->paymentInstructions->hydrateXmlDocument($invoiceXML);
        }

        $supplyChainTradeTransaction->appendChild($applicableHeaderTradeSettlement);

        if (ProcessControl::MINIMUM !== $this->processControl->getSpecificationIdentifier()) {
            foreach ($this->vatBreakdowns as $vatBreakdown) {
                $vatBreakdown->hydrateXmlDocument($invoiceXML);
            }
        }

        $specifiedTradePaymentTerms = $invoiceXML->createElement('ram:SpecifiedTradePaymentTerms');

        $paymentDueDate = $invoiceXML->createElement('ram:DueDateDateTime');
        $paymentDueDateString = $invoiceXML->createElement('udt:DateTimeString', $this->paymentDueDate->format('Ymd'));
        $paymentDueDateString->setAttribute('format', '102');
        $paymentDueDate->appendChild($paymentDueDateString);

        $specifiedTradePaymentTerms->appendChild($paymentDueDate);
        $applicableHeaderTradeSettlement->appendChild($specifiedTradePaymentTerms);

        $this->documentTotals->hydrateXmlDocument($invoiceXML, $this->processControl->getSpecificationIdentifier());


        return $invoiceXML;
    }

    private function appendToExchangedDocument(\DOMDocument $invoice, \DOMElement $child): void
    {
        $invoice->getElementsByTagName('rsm:ExchangedDocument')->item(0)->appendChild($child);
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getIssueDate(): \DateTimeInterface
    {
        return $this->issueDate;
    }

    public function getTypeCode()
    {
        return $this->typeCode;
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function getVatAccountingCurrencyCode()
    {
        return $this->vatAccountingCurrencyCode;
    }

    public function getValueAddedTaxPointDate(): \DateTimeInterface
    {
        return $this->valueAddedTaxPointDate;
    }

    public function getValueAddedTaxPointDateCode()
    {
        return $this->valueAddedTaxPointDateCode;
    }

    public function getPaymentDueDate(): \DateTimeInterface
    {
        return $this->paymentDueDate;
    }

    public function getBuyerReference()
    {
        return $this->buyerReference;
    }

    public function getProjectReference()
    {
        return $this->projectReference;
    }

    public function getContractReference()
    {
        return $this->contractReference;
    }

    public function getPurchaseOrderReference()
    {
        return $this->purchaseOrderReference;
    }

    public function getSalesOrderReference()
    {
        return $this->salesOrderReference;
    }

    public function getReceivingAdviceReference()
    {
        return $this->receivingAdviceReference;
    }

    public function getDespatchAdviceReference()
    {
        return $this->despatchAdviceReference;
    }

    public function getTenderOrLotReference()
    {
        return $this->tenderOrLotReference;
    }

    public function getBuyerAccountingReference(): string
    {
        return $this->buyerAccountingReference;
    }

    public function getPaymentTerms(): string
    {
        return $this->paymentTerms;
    }

    public function getInvoiceNote(): array
    {
        return $this->invoiceNote;
    }

    public function getProcessControl(): ProcessControl
    {
        return $this->processControl;
    }

    public function getPrecedingInvoiceReference(): array
    {
        return $this->precedingInvoiceReference;
    }

    public function getSeller(): Seller
    {
        return $this->seller;
    }

    public function getBuyer(): Buyer
    {
        return $this->buyer;
    }

    public function getPayee(): Payee
    {
        return $this->payee;
    }

    public function getSellerTaxRepresentativeParty(): SellerTaxRepresentativeParty
    {
        return $this->sellerTaxRepresentativeParty;
    }

    public function getDeliveryInformation(): DeliveryInformation
    {
        return $this->deliveryInformation;
    }

    public function getPaymentInstructions(): PaymentInstructions
    {
        return $this->paymentInstructions;
    }

    public function getDocumentLevelAllowances(): array
    {
        return $this->documentLevelAllowances;
    }

    public function getDocumentLevelCharges(): array
    {
        return $this->documentLevelCharges;
    }

    public function getDocumentTotals(): DocumentTotals
    {
        return $this->documentTotals;
    }

    public function getVatBreakdowns(): array
    {
        return $this->vatBreakdowns;
    }

    public function getAdditionalSupportingDocuments(): array
    {
        return $this->additionalSupportingDocuments;
    }

    public function getInvoiceLines(): array
    {
        return $this->invoiceLines;
    }

    public function setBuyerReference(?string $buyerReference): self
    {
        $this->buyerReference = $buyerReference;

        return $this;
    }

    public function addIncludedNote(InvoiceNote ...$note): self
    {
        $this->invoiceNote = $note;

        return $this;
    }
}
