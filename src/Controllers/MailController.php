<?php

namespace App\Http\Controllers;

use App\Services\MailService;
use Illuminate\Http\Request;

class MailController
{
    /**
     * @var MailService
     */
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function sendFromPublic(Request $request)
    {
        $input = $request->all();

        $input['recipient-address'] = env('MAIL_DEFAULT_RECIPIENT') ?? MAIL_DEFAULT_RECIPIENT;

        return $this->send($input);
    }

    /*
     * $input['recipient-address']
     * $input['subject']
     * $input['sender']
     * $input['sender-name']
     * $input['reply-to']
     * $input['type']
     * $input['subject']
     * $input['error-message']
     * $input['success-message']
    */
    public function sendFromMember(Request $request)
    {
        $input = $request->all();

        return $this->send($input);
    }

    private function send($input){
        $result = $this->mailService->send($input);

        if($result === false){
            $errorMessage = "Error while trying to send. Please send your request directly to support, and we'll " .
                "ensure it gets to the correct person.";
            if($input['error-message']){
                $errorMessage = $input['error-message'];
            }

            return redirect()->back()->with('error-message', $errorMessage);
        }

        $successMessage = "Email sent";
        if($input['success-message']){
            $successMessage = $input['success-message'];
        }

        return redirect()->back()->with('success-message', $successMessage);
    }
}