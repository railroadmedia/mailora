<?php

namespace App\Services;

use App\Mail\General;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    const MAIL_NAMESPACE = 'App\Mail\\';
    const EMAIL_TEMPLATE_DIR_NAME = 'emails';

    const TYPE_CLASS_MAP = [
        'general' => 'General'
    ];

    /**
     * @param $input array
     * @param $returnExceptionObjectOnFailure null|bool
     *
     * @return bool|Exception
     *
     *  Good values to pass include but are not limited to:
     *      'recipient-address'
     *      'subject'
     *      'sender'
     *      'sender-name'
     *      'reply-to'
     *      'type'
     *      'error-message'
     *      'success-message'
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
        if(auth()->user()){
            $input['user'] = auth()->user();
        }
        $input['message'] = !empty($input['message']) ? $input['message'] : '';
        $type = !empty($input['type']) ? $input['type'] : 'general';

        $email = $this->getEmailInstance($input, $type);

        if($type === 'ime-question'){
            $input['recipient-address'] = 'jared+ime_2018@drumeo.com';
        }

        if($type === 'support'){
            $input['recipient-address'] = 'support@drumeo.com';
        }

        if($type === 'ask-question'){
            $input['recipient-address'] = 'questions@drumeo.com';
        }

        if($type === 'student-focus-application'){
            $input['recipient-address'] = 'questions@drumeo.com';
        }

        $this->setSender($input, $email);
        $this->setRecipient($input, $email);

        $email->subject(!empty($input['subject']) ? $input['subject'] : 'General Inquiry - Subject not specified');

        if($input['reply-to'] !== null){
            /*
             * to not have a reply-to set, pass a boolean false. Passing no string will result in that being set as
             * reply-to, otherwise, the user's email address will be set.
             */
            if($input['reply-to'] !== false){
                $email->replyTo($input['reply-to']);
            }
        }else{
            if($input['user']){
                $email->replyTo($input['user']->email);
            }
        }

        return $email;
    }

    private function getEmailInstance($input, $type)
    {
        $view = $this::EMAIL_TEMPLATE_DIR_NAME . '.' . $type;

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
        $senderAddress = env('MAIL_DEFAULT_SENDER_ADDRESS') ?? MAIL_DEFAULT_SENDER_ADDRESS;
        $senderName = env('MAIL_DEFAULT_SENDER_NAME') ?? MAIL_DEFAULT_SENDER_NAME;

        if (!empty($input['sender-address'])) {

            $senderAddress = $input['sender-address'];

            if (!empty($input['sender-name'])) {
                $senderName = $input['sender-name'];
            }
        }

        $email->from($senderAddress, $senderName);
    }

    private function setRecipient($input, Mailable &$email)
    {
        $recipientAddress = $input['recipient-address'];

        if(app()->environment() !== 'production') {
            $recipientAddress = env('MAIL_SAFETY_RECIPIENT') ?? MAIL_SAFETY_RECIPIENT;
        }

        $addressSetButNotName = !empty($input['recipient-address']) && empty($input['recipient-name']);
        $addressAndNameSet = !empty($input['recipient-address']) && !empty($input['recipient-name']);

        if ($addressAndNameSet) {
            $email->to($recipientAddress, $input['recipient-name']);
        }else{
            if ($addressSetButNotName) {
                $email->to($recipientAddress);
            }else{
                $email->to(env('MAIL_DEFAULT_RECIPIENT') ?? MAIL_DEFAULT_RECIPIENT);
            }
        }
    }
}