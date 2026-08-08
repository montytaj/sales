<?php

namespace App\Services;

class SalesCalculationService
{
    /**
     * Calculate line item totals on the server.
     *
     * @param float $quantity
     * @param float $unitPrice
     * @param float $discountAmount
     * @param float $taxPercent
     * @return array
     */
    public function calculateItem(float $quantity, float $unitPrice, float $discountAmount = 0.0, ?float $taxPercent = null): array
    {
        $taxPercent = $taxPercent ?? (float) setting('tax_percentage', 15.00);
        $rawSubtotal = round($quantity * $unitPrice, 2);
        $discount = min($discountAmount, $rawSubtotal);
        $subtotalAfterDiscount = max(0, $rawSubtotal - $discount);

        $taxAmount = round(($subtotalAfterDiscount * $taxPercent) / 100, 2);
        $total = round($subtotalAfterDiscount + $taxAmount, 2);

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount_amount' => $discount,
            'tax_percent' => $taxPercent,
            'subtotal' => $subtotalAfterDiscount,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ];
    }

    /**
     * Calculate overall document totals from calculated items array.
     *
     * @param array $calculatedItems
     * @return array
     */
    public function calculateDocumentTotals(array $calculatedItems): array
    {
        $subtotal = 0.0;
        $discountAmount = 0.0;
        $taxAmount = 0.0;
        $totalAmount = 0.0;

        foreach ($calculatedItems as $item) {
            $subtotal += $item['subtotal'];
            $discountAmount += $item['discount_amount'];
            $taxAmount += $item['tax_amount'];
            $totalAmount += $item['total'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discountAmount, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }
}
