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

    public function send(Request $request){
        $input = $request->all();
        $sent = $this->mailService->send($input);

        return JsonResponse::create(['sent' => $sent]);
    }
}