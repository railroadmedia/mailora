<?php

namespace Railroad\Mailora\Services;

use App\Mail\General;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
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

        if($email === false){
            return false;
        }

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
     * @return Mailable|bool
     */
    private function makeEmailObject($input)
    {
        $email = $this->getEmailInstance($input);

        if($email === false){
            return false;
        }

        $this->setSender($input, $email);
        $this->setRecipient($input, $email);
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);

        // if no message defined, make sure email doesn't break
        $input['message'] = !empty($input['message']) ? $input['message'] : '';

        return $email;
    }

    /**
     * @param $input
     * @param $type
     *
     * @return Mailable|bool $email
     */
    private function getEmailInstance($input)
    {
        // 0. get custom-namespace

        // Ensure config-provided custome namespace has backslash on end.
        $customNamespace = config('mailora.mailables-namespace');
        if(!empty($customNamespace)){
            $endsWithBackslash = substr($customNamespace, -1) === '\\';
            if(!$endsWithBackslash){
                $customNamespace = $customNamespace . '\\';
            }
        }

        // 1. get type

        $type = config('mailora.defaults.type');

        if(!empty($input['type'])){
            $type = $input['type'];
        }

        // 2. get views and classes

        if($type === 'general'){

            // default to native version
            $view = '/laravel/vendor/railroad/mailora/resources/views/general.blade.php'; // how to best specify this path??
            $view = '/laravel/vendor/railroad/mailora/resources/views/general.blade.php'; // how to best specify this path??
            $view = '/laravel/vendor/railroad/mailora/resources/views/general.blade.php'; // how to best specify this path??
            $view = '/laravel/vendor/railroad/mailora/resources/views/general.blade.php'; // how to best specify this path??
            $view = '/laravel/vendor/railroad/mailora/resources/views/general.blade.php'; // how to best specify this path??
            $view = '/laravel/vendor/railroad/mailora/resources/views/general.blade.php'; // how to best specify this path??
            $emailClass = '\Railroad\Mailora\Mail\General';

            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑ 
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑
            $viewFileExists = file_exists($view); // why doesn't this work? How to best define ↑↑↑↑↑↑

            // assuming (class_exists($emailClass) === true) ...

            // overwrite with custom if provided
            $customGeneralView = config('mailora.views-directory') . '/' . $type . '.blade.php';
            if (file_exists($customGeneralView)) {
                $view = $customGeneralView;
            }

            // see if custom General Mailable class exists
            if($customNamespace){
                $customGeneralClass = $customNamespace . 'General';
                if(class_exists($customGeneralClass)){
                    $emailClass = $customGeneralClass;
                }
            }

        }else{ // if $type !== 'general'

            // get view
            $customPotentialView = $_SERVER["DOCUMENT_ROOT"] . config('mailora.views-directory') . '/' . $type . '.blade.php';
            if (file_exists($customPotentialView)) {
                $view = $customPotentialView;
            }else{
                $message = 'Custom type specified does have corresponding custom view. Email not sent. ';
                $message .= json_encode($input);
                error_log($message);
                $this->sendErrorMessageToAdmin($message);
                return false;
            }

            // get class
            $potentialClass = $this->dashesToCamelCase($type, true);
            if($customNamespace){
                $customClassForCustomType = $customNamespace . $potentialClass;
                if(class_exists($customClassForCustomType)){
                    $emailClass = $customClassForCustomType;
                }
            }
        }

        // 3. create Mailable instance

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

    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace('-', '', ucwords($string, '-'));

        if (!$capitalizeFirstCharacter) {
            $str = lcfirst($str);
        }

        return $str;
    }

    private function sendErrorMessageToAdmin($message){
        // todo maybe?
        // todo maybe?
        // todo maybe?
    }
}