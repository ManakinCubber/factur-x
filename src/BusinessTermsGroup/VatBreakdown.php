<?php

namespace Tiime\FacturX\BusinessTermsGroup;

use Tiime\FacturX\DataType\VatCategory;
use Tiime\FacturX\DataType\VatExoneration;

/**
 * BG-23
 * A group of business terms providing information about VAT breakdown by different
 * categories, rates and exemption reasons.
 */
class VatBreakdown
{
    /**
     * BT-116
     * Sum of all taxable amounts subject to a specific VAT category code and VAT category rate
     * (if the VAT category rate is applicable).
     *
     * Somme de tous les montants soumis à taxes assujettis à un code et à un taux de type de
     * TVA spécifiques (si le Taux de type de TVA est applicable).
     */
    private float $vatCategoryTaxableAmount;

    /**
     * BT-117
     * The total VAT amount for a given VAT category.
     *
     * Montant total de la TVA pour un type donné de TVA.
     */
    private float $vatCategoryTaxAmount;

    /**
     * BT-118
     * Coded identification of a VAT category.
     *
     * Identification codée d'un type de TVA.
     */
    private VatCategory $vatCategoryCode;

    /**
     * BT-119
     * The VAT rate, represented as percentage that applies for the relevant VAT category.
     *
     * Taux de TVA, exprimé sous forme de pourcentage, applicable au type de TVA correspondant.
     */
    private ?float $vatCategoryRatePercent;

    /**
     * BT-120
     * A textual statement of the reason why the amount is exempted from VAT or why no VAT is being charged
     *
     * Énoncé expliquant pourquoi un montant est exonéré de TVA ou pourquoi la TVA n'est pas appliquée.
     */
    private ?string $vatExemptionReasonText;

    /**
     * BT-121
     * A coded statement of the reason for why the amount is exempted from VAT.
     *
     * Énoncé codé expliquant pourquoi un montant est exonéré de TVA.
     */
    private ?VatExoneration $vatExemptionReasonCode;

    public function __construct(
        float $vatCategoryTaxableAmount,
        float $vatCategoryTaxAmount,
        VatCategory $vatCategoryCode
    ) {
        $this->vatCategoryTaxableAmount = $vatCategoryTaxableAmount;
        $this->vatCategoryTaxAmount = $vatCategoryTaxAmount;
        $this->vatCategoryCode = $vatCategoryCode;

        $this->vatCategoryRatePercent = null;
        $this->vatExemptionReasonText = null;
        $this->vatExemptionReasonCode = null;
    }

    public function getVatCategoryTaxableAmount(): float
    {
        return $this->vatCategoryTaxableAmount;
    }

    public function setVatCategoryTaxableAmount(float $vatCategoryTaxableAmount): self
    {
        $this->vatCategoryTaxableAmount = $vatCategoryTaxableAmount;

        return $this;
    }

    public function getVatCategoryTaxAmount(): float
    {
        return $this->vatCategoryTaxAmount;
    }

    public function setVatCategoryTaxAmount(float $vatCategoryTaxAmount): self
    {
        $this->vatCategoryTaxAmount = $vatCategoryTaxAmount;

        return $this;
    }

    public function getVatCategoryCode(): VatCategory
    {
        return $this->vatCategoryCode;
    }

    public function setVatCategoryCode(VatCategory $vatCategoryCode): self
    {
        $this->vatCategoryCode = $vatCategoryCode;

        return $this;
    }

    public function getVatCategoryRatePercent(): ?float
    {
        return $this->vatCategoryRatePercent;
    }

    public function setVatCategoryRatePercent(?float $vatCategoryRatePercent): self
    {
        $this->vatCategoryRatePercent = $vatCategoryRatePercent;

        return $this;
    }

    public function getVatExemptionReasonText(): ?string
    {
        return $this->vatExemptionReasonText;
    }

    public function setVatExemptionReasonText(?string $vatExemptionReasonText): self
    {
        $this->vatExemptionReasonText = $vatExemptionReasonText;

        return $this;
    }

    public function getVatExemptionReasonCode(): ?VatExoneration
    {
        return $this->vatExemptionReasonCode;
    }

    public function setVatExemptionReasonCode(?VatExoneration $vatExemptionReasonCode): self
    {
        $this->vatExemptionReasonCode = $vatExemptionReasonCode;

        return $this;
    }



    public function hydrateXmlDocument(\DOMDocument $document): void
    {
        $applicableHeaderTradeSettlement = $document
            ->getElementsByTagName('ram:ApplicableHeaderTradeSettlement')
            ->item(0);

        $applicableTradeTax = $document->createElement('ram:ApplicableTradeTax');
        $applicableTradeTax->appendChild(
            $document->createElement('ram:CalculatedAmount', $this->vatCategoryTaxAmount)
        );
        $applicableTradeTax->appendChild($document->createElement('ram:TypeCode', "VAT"));
        $applicableTradeTax->appendChild(
            $document->createElement('ram:BasisAmount', $this->vatCategoryTaxableAmount)
        );
        $applicableTradeTax->appendChild(
            $document->createElement('ram:CategoryCode', $this->vatCategoryCode->value)
        );

        if (false) { // @todo
            $applicableTradeTax->appendChild(
                $document->createElement('ram:DueDateTypeCode')
            );
        }

        if (null !== $this->vatCategoryRatePercent) {
            $applicableTradeTax->appendChild(
                $document->createElement('ram:RateApplicablePercent', $this->vatCategoryRatePercent)
            );
        }

        if (null !== $this->vatExemptionReasonCode) {
            $applicableTradeTax->appendChild(
                $document->createElement('ram:ExemptionReasonCode', $this->vatExemptionReasonCode->value)
            );
        }

        if (null !== $this->vatExemptionReasonText) {
            $applicableTradeTax->appendChild(
                $document->createElement('ram:ExemptionReason', $this->vatExemptionReasonText)
            );
        }

        $applicableHeaderTradeSettlement->appendChild($applicableTradeTax);
    }
}
