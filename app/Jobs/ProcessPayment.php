<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\VodacomService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $amount;
    protected $phoneNumber;
    protected $order;

    /**
     * Create a new job instance.
     */
    public function __construct($amount, $phoneNumber, Order $order)
    {
        $this->amount = $amount;
        $this->phoneNumber = $phoneNumber;
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(VodacomService $vodacomService)
    {
        $vodacomService->makeC2BPayment($this->amount, $this->phoneNumber, $this->order);
    }
}
