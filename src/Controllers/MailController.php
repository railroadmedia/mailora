<?php

namespace Railroad\Mailora\Controllers;

use Illuminate\Http\JsonResponse;
use Railroad\Mailora\Services\MailService;
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

        $sent = $this->send($input);

//        $sent = true;

        return JsonResponse::create(['sent' => $sent]);
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