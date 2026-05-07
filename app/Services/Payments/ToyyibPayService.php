<?php

namespace App\Services\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ToyyibPayService
{
    public function createBill(Order $order): string
    {
        $baseUrl = rtrim(config('services.toyyibpay.base_url'), '/');

        $response = Http::asForm()->post($baseUrl . '/index.php/api/createBill', [
            'userSecretKey' => config('services.toyyibpay.secret_key'),
            'categoryCode' => config('services.toyyibpay.category_code'),

            'billName' => $order->product_name,
            'billDescription' => $this->buildDescription($order),
            'billPriceSetting' => 1,
            'billPayorInfo' => 1,
            'billAmount' => $order->total_amount,

            'billReturnUrl' => route('payment.toyyibpay.return'),
            'billCallbackUrl' => route('webhook.toyyibpay'),

            'billExternalReferenceNo' => $order->order_number,
            'billTo' => $order->customer_name,
            'billEmail' => $order->customer_email,
            'billPhone' => $order->customer_phone,

            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => 0,
            'billContentEmail' => 'Thank you for your order.',
            'billChargeToCustomer' => 1,
        ]);

        if ($response->failed()) {
            Log::error('ToyyibPay createBill failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException('Unable to create ToyyibPay bill.');
        }

        $body = $this->normalizeResponseBody($response->body());
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('ToyyibPay createBill missing BillCode', [
                'order_id' => $order->id,
                'response_body' => $body,
            ]);

            throw new RuntimeException('ToyyibPay did not return a bill code.');
        }

        $billCode = $this->extractBillCode($data);

        if ($billCode === null) {
            Log::error('ToyyibPay createBill missing BillCode', [
                'order_id' => $order->id,
                'response_body' => $body,
                'response' => $data,
            ]);

            throw new RuntimeException('ToyyibPay did not return a bill code.');
        }

        $order->update([
            'toyyibpay_bill_code' => $billCode,
        ]);

        return $baseUrl . '/' . $billCode;
    }

    private function extractBillCode(mixed $data): ?string
    {
        if (! is_array($data)) {
            return null;
        }

        $payloads = [$data];

        if (isset($data[0]) && is_array($data[0])) {
            $payloads[] = $data[0];
        }

        foreach ($payloads as $payload) {
            foreach (['BillCode', 'billCode', 'billcode'] as $key) {
                if (isset($payload[$key]) && is_string($payload[$key]) && $payload[$key] !== '') {
                    return $payload[$key];
                }
            }
        }

        return null;
    }

    private function normalizeResponseBody(string $body): string
    {
        $body = preg_replace('/^\xEF\xBB\xBF/', '', $body);

        return trim($body ?? '');
    }

    private function buildDescription(Order $order): string
    {
        return implode("\n", [
            $order->product_name,
            'Order: ' . $order->order_number,
            'Size: ' . $order->size,
            'Quantity: ' . $order->quantity,
        ]);
    }
}
