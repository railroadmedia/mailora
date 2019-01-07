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

    public function sendPublic(Request $request)
    {
        $input = $request->all();
        try {
            $sent = $this->mailService->sendPublic($input);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return JsonResponse::create(['error' => true], 500);
        }

        return JsonResponse::create(['sent' => $sent], $sent ? 200 : 500);
    }

    public function sendSecure(Request $request)
    {
        $input = $request->all();
        try {
            $sent = $this->mailService->sendSecure($input);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            return JsonResponse::create(['error' => true], 500);
        }

        return JsonResponse::create(['sent' => $sent], $sent ? 200 : 500);
    }
}