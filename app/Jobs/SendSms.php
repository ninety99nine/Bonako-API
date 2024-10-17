<?php

namespace App\Jobs;

use App\Models\Store;
use Illuminate\Bus\Queueable;
use App\Services\Sms\SmsService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $content;
    public $senderName;
    public $clientCredentials;
    public $senderMobileNumber;
    public $recipientMobileNumber;

    /**
     * Create a new job instance.
     *
     *  @param string $content - The message Model
     *  @param string $recipientMobileNumber - The number of the recipient to receive the sms e.g 26772000001
     *  @param string|Store|null $senderName - The name of the sender sending the sms e.g Company XYZ
     *  @param string|null $senderMobileNumber - The number of the sender sending the sms e.g 26772000001
     *  @param string|null $clientCredentials - The client credentials used for authentication (Provided by Orange BW)
     *
     * @return void
     */
    public function __construct($content, $recipientMobileNumber, $senderName = null, $senderMobileNumber = null, $clientCredentials = null)
    {
        $this->content = $content;
        $this->senderName = $senderName;
        $this->clientCredentials = $clientCredentials;
        $this->senderMobileNumber = $senderMobileNumber;
        $this->recipientMobileNumber = $recipientMobileNumber;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        SmsService::sendOrangeSms(
            $this->content,
            $this->recipientMobileNumber,
            $this->senderName, $this->senderMobileNumber, $this->clientCredentials
        );
    }
}
