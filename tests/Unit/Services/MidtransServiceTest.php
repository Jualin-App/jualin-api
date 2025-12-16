<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\MidtransService;
use Mockery;
use Illuminate\Support\Str;

class MidtransServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function it_creates_snap_token_successfully_path_alpha()
    {
        $paymentClass = 'App\Models\Payment';
        $transactionClass = 'App\Models\Transaction';
        $userClass = 'App\Models\User';
        $productClass = 'App\Models\Product';
        $snapClass = 'Midtrans\Snap';

        $transactionId = 1;
        $totalAmount = 100000;

        $paymentMock = Mockery::mock('overload:' . $paymentClass);
        $snapMock = Mockery::mock('overload:' . $snapClass);

        $paymentInstance = Mockery::mock();
        $paymentInstance->id = 123;
        $paymentInstance->shouldReceive('update')->once()->with(Mockery::on(function ($arg) {
            return isset($arg['snap_token']) && isset($arg['snap_url']);
        }));
        $paymentInstance->shouldReceive('getAttribute')->with('id')->andReturn(123);

        $paymentMock->shouldReceive('create')->once()->with(Mockery::on(function ($arg) use ($transactionId, $totalAmount) {
            return strpos($arg['order_id'], 'ORDER-') === 0 &&
                $arg['transaction_id'] === $transactionId &&
                $arg['gross_amount'] === $totalAmount &&
                $arg['transaction_status'] === 'pending';
        }))->andReturn($paymentInstance);

        $snapResponse = (object) ['token' => 'test-snap-token', 'redirect_url' => 'http://example.com/redirect'];
        $snapMock->shouldReceive('createTransaction')->once()->andReturn($snapResponse);

        $user = Mockery::mock($userClass);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('John Doe');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('john@example.com');

        $product = Mockery::mock($productClass);
        $product->shouldReceive('getAttribute')->with('name')->andReturn('Test Product');

        $item = Mockery::mock(\stdClass::class);
        $item->product_id = 1;
        $item->product = $product;
        $item->price_at_purchase = 100000;
        $item->quantity = 1;

        $transaction = Mockery::mock($transactionClass);
        $transaction->shouldReceive('getAttribute')->with('id')->andReturn($transactionId);
        $transaction->shouldReceive('getAttribute')->with('total_amount')->andReturn($totalAmount);
        $transaction->shouldReceive('getAttribute')->with('items')->andReturn([$item]);
        $transaction->shouldReceive('getAttribute')->with('customer')->andReturn($user);

        $service = new MidtransService();
        $customerDetails = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
        ];

        $result = $service->createSnapToken($transaction, $customerDetails);

        $this->assertEquals('test-snap-token', $result['snap_token']);
        $this->assertEquals('http://example.com/redirect', $result['snap_url']);
        $this->assertEquals(123, $result['payment_id']);
    }

    public function it_handles_midtrans_exception_gracefully_path_beta()
    {
        $paymentClass = 'App\Models\Payment';
        $snapClass = 'Midtrans\Snap';
        $transactionClass = 'App\Models\Transaction';
        $productClass = 'App\Models\Product';

        $paymentMock = Mockery::mock('overload:' . $paymentClass);
        $snapMock = Mockery::mock('overload:' . $snapClass);

        $paymentInstance = Mockery::mock();
        $paymentMock->shouldReceive('create')->andReturn($paymentInstance);

        $snapMock->shouldReceive('createTransaction')->andThrow(new \Exception('Midtrans Error'));

        $transaction = Mockery::mock($transactionClass);
        $transaction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $transaction->shouldReceive('getAttribute')->with('total_amount')->andReturn(100000);

        $item = Mockery::mock(\stdClass::class);
        $item->product_id = 1;
        $item->product = Mockery::mock($productClass);
        $item->product->shouldReceive('getAttribute')->with('name')->andReturn('P');
        $item->price_at_purchase = 100000;
        $item->quantity = 1;

        $transaction->shouldReceive('getAttribute')->with('items')->andReturn([$item]);
        $transaction->shouldReceive('getAttribute')->with('customer')->andReturn(null);

        $service = new MidtransService();
        $customerDetails = [
            'first_name' => 'A',
            'email' => 'test@example.com',
            'phone' => '08123'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to create payment token: Midtrans Error');

        $service->createSnapToken($transaction, $customerDetails);
    }

    public function it_uses_fallback_customer_details_path_gamma()
    {
        $paymentClass = 'App\Models\Payment';
        $snapClass = 'Midtrans\Snap';
        $transactionClass = 'App\Models\Transaction';
        $userClass = 'App\Models\User';
        $productClass = 'App\Models\Product';

        $paymentMock = Mockery::mock('overload:' . $paymentClass);
        $snapMock = Mockery::mock('overload:' . $snapClass);

        $paymentInstance = Mockery::mock();
        $paymentInstance->id = 999;
        $paymentInstance->shouldReceive('update');
        $paymentInstance->shouldReceive('getAttribute')->with('id')->andReturn(999);

        $paymentMock->shouldReceive('create')->andReturn($paymentInstance);

        $snapResponse = (object) ['token' => 'tok', 'redirect_url' => 'url'];

        $snapMock->shouldReceive('createTransaction')->once()->with(Mockery::on(function ($params) {
            $customerDetails = $params['customer_details'];
            return $customerDetails['first_name'] === 'Fallback Name' &&
                   $customerDetails['email'] === 'fallback@example.com';
        }))->andReturn($snapResponse);

        $user = Mockery::mock($userClass);
        $user->shouldReceive('getAttribute')->with('name')->andReturn('Fallback Name');
        $user->shouldReceive('getAttribute')->with('email')->andReturn('fallback@example.com');

        $transaction = Mockery::mock($transactionClass);
        $transaction->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $transaction->shouldReceive('getAttribute')->with('total_amount')->andReturn(500);

        $item = Mockery::mock(\stdClass::class);
        $item->product_id = 1;
        $item->product = Mockery::mock($productClass);
        $item->product->shouldReceive('getAttribute')->with('name')->andReturn('P');
        $item->price_at_purchase = 500;
        $item->quantity = 1;

        $transaction->shouldReceive('getAttribute')->with('items')->andReturn([$item]);
        $transaction->shouldReceive('getAttribute')->with('customer')->andReturn($user);

        $service = new MidtransService();
        $customerDetails = [];

        $result = $service->createSnapToken($transaction, $customerDetails);

        $this->assertIsArray($result);
        $this->assertEquals('tok', $result['snap_token']);
    }
}
