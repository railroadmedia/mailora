<?php

namespace Railroad\Mailora\Controllers;

use Railroad\Mailora\Services\MailService;
use Illuminate\Http\Request;

class MailController
{
    /**
     * @var MailService
     */
    private $mailService;
    private $returnErrorDetailsOnFailure;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;

        $this->returnErrorDetailsOnFailure = false;
        $production = app()->environment() === config('mailora.name-of-production-env');
        if (!$production) {
            $this->returnErrorDetailsOnFailure = true;
        }
    }

    public function sendPublic(Request $request)
    {
        $input = $request->all();
        try {
            $this->mailService->sendPublic($input);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            if ($this->returnErrorDetailsOnFailure) {
                return response()->json(['error' => 'see error_log for details'], 500);
            }
        }

        return response()->json(['sent' => true], 200);
    }


    public function sendSecure(Request $request)
    {
        $input = $request->all();
        try {
            $this->mailService->sendSecure($input);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            if ($this->returnErrorDetailsOnFailure) {
                return response()->json(['error' => $exception->getMessage()], 500);
            }
            return response()->json(['error' => $exception->getMessage()], 500);
        }
        return response()->json(['sent' => true], 200);
    }
}