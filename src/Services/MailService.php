<?php

namespace Railroad\Mailora\Services;

use App\Mail\General;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    const MAIL_NAMESPACE = 'App\Mail\\';

//    const EMAIL_TEMPLATE_DIR_NAME = 'emails';
    const EMAIL_TEMPLATE_DIR_NAME = '';

    const TYPE_CLASS_MAP = [
        'general' => 'General'
    ];

    /**
     * MailService constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->ensureConfigSet();
    }

    /**
     * @throws Exception
     */
    private function ensureConfigSet(){
        $recipientSafety = config('mailora.defaults.safety-recipient');
        $senderAddress = config('mailora.defaults.sender-address');
        $senderName = config('mailora.defaults.sender-name');
        $recipientAddress = config('mailora.defaults.recipient-address');
        $nameOfProductionEnv = config('mailora.defaults.name-of-production-env');

        if(
            empty($recipientSafety) ||
            empty($senderAddress) ||
            empty($senderName) ||
            empty($recipientAddress) ||
            empty($nameOfProductionEnv)
        ){
            $notSet = [];

            if(empty($recipientSafety)){
                $notSet[] = 'safety-recipient';
            }
            if(empty($senderAddress)){
                $notSet[] = 'sender-address';
            }
            if(empty($senderName)){
                $notSet[] = 'sender-name';
            }
            if(empty($recipientAddress)){
                $notSet[] = 'recipient-address';
            }
            if(empty($nameOfProductionEnv)){
                $notSet[] = 'name-of-production-env';
            }
            throw new Exception(
                'Required Mailora config (mailora.defaults) values not set (' . implode(', ', $notSet) . ')'
            );

        }
    }

    /**
     * @param $input array
     * @param $returnExceptionObjectOnFailure null|bool
     *
     * @return bool|Exception
     */
    public function send($input, $returnExceptionObjectOnFailure = false){
        $email = $this->makeEmailObject($input);
        try{
            Mail::send($email);
        }catch(Exception $exception){
            error_log(
                'Email failed with message: "' . $exception->getMessage() . '". Email input ' .
                '(passed through json_encode): "' . json_encode($input) . '"'
            );
            return $returnExceptionObjectOnFailure ? $exception : false;
        }
        return true;
    }

    // -----------------------------------------------------------

    /**
     * @param $input array
     * @return Mailable
     */
    private function makeEmailObject($input)
    {
        $type = !empty($input['type']) ? $input['type'] : config('mailora.defaults.type');

        $email = $this->getEmailInstance($input, $type);

        $this->setSender($input, $email);
        $this->setRecipient($input, $email);
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);

        // if no message defined, make sure email doesn't break
        $input['message'] = !empty($input['message']) ? $input['message'] : '';

        return $email;
    }

    private function getEmailInstance($input, $type)
    {
//        $view = $this::EMAIL_TEMPLATE_DIR_NAME . '.' . $type;
        $view = !empty($this::EMAIL_TEMPLATE_DIR_NAME) ? $this::EMAIL_TEMPLATE_DIR_NAME . '.' . $type : $type;

        $typeMatchesAnAvailableClass = $this::TYPE_CLASS_MAP[$type];

        if($typeMatchesAnAvailableClass) {
            $emailClass = $this::MAIL_NAMESPACE . $this::TYPE_CLASS_MAP[$type];
        }else{
            /*
             * If class for type doesn't exist, default to 'General'.
             * Set view as 'general', but then check for—and use if available—a type-specific view.
             *
             * This enables front-end developers to create new types without having to muck around creating a new class.
             * They can just create the view, and then pass that 'type' value to access that view.
             */

            /** @var General $emailClass */
            $emailClass = $this::MAIL_NAMESPACE . 'General';

            $view = $this::EMAIL_TEMPLATE_DIR_NAME . '.general';
            $emailTemplateFileToLookFor = $_SERVER["DOCUMENT_ROOT"] . '/laravel/resources/views/' .
                $this::EMAIL_TEMPLATE_DIR_NAME . '/' . $type . '.blade.php';

            if (file_exists($emailTemplateFileToLookFor)) {
                $view = $this::EMAIL_TEMPLATE_DIR_NAME . '.' . $type;
            }
        }

        return new $emailClass($input, $view);
    }

    private function setSender($input, Mailable &$email)
    {
        $senderAddress = config('mailora.defaults.sender-address');
        $senderName = config('mailora.defaults.sender-name');

        if (!empty($input['sender-address'])) {
            $senderAddress = $input['sender-address'];
            $senderName = null;

            if(!empty($input['sender-name'])){
                $senderName = $input['sender-name'];
            }
        }

        $email->from($senderAddress, $senderName);
    }

    private function setRecipient($input, Mailable &$email)
    {
        // PART 1 - determine value to set as recipient

        // 1.1. default to default
        $recipientAddress = $email->to(config('mailora.defaults.recipient-address'));

        // 1.2 if input provided, use that
        if(!empty($input['recipient-address'])){
            $recipientAddress = $input['recipient-address'];
        }

        // 1.3 if not prod, discard previous and use safety
        $production = app()->environment() === config('mailora.defaults.name-of-production-env');
        if(!$production) {
            $recipientAddress = config('mailora.defaults.safety-recipient');
        }

        // PART 2 - set it

        if (!empty($input['recipient-name'])) {
            $email->to($recipientAddress, $input['recipient-name']);
        }else{ // must use else, or else will set *two* recipients, one with name, one without.
            $email->to($recipientAddress);
        }
    }

    private function setSubject($input, Mailable &$email){
        $subject = 'General Inquiry - Subject not specified';

        if(config('mailora.defaults.subject')){
            $subject = config('mailora.defaults.subject');
        }

        if(!empty($input['subject'])){
            $subject = $input['subject'];
        }

        $email->subject($subject);
    }

    private function setReplyTo($input, Mailable &$email){
        if(!empty($input['reply-to'])){
            $email->replyTo($input['reply-to']);
        }else{
            $user = auth()->user();
            if($user && config('mailora.defaults.users-email-set-reply-to')){
                $email->replyTo($user->email);
            }
        }
    }
}