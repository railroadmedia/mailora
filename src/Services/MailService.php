<?php

namespace Railroad\Mailora\Services;

use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * @param bool $public
     * @throws Exception
     */
    private function ensureConfigSet($public = false)
    {
        $recipientSafety = config('mailora.safety-recipient');
        $senderAddress = config('mailora.defaults.sender-address');
        $recipientAddress = config('mailora.defaults.recipient-address');
        $nameOfProductionEnv = config('mailora.name-of-production-env');
        $approvedRecipients = config('mailora.approved-recipients');
        $approvedRecipientDomains = config('mailora.approved-recipient-domains');

        $failPublic = false;

        if ($public) {
            $failPublic = (empty($approvedRecipients) && empty($approvedRecipientDomains)); // only one of these required
        }

        if (
            empty($recipientSafety) ||
            empty($senderAddress) ||
            empty($recipientAddress) ||
            empty($nameOfProductionEnv) ||
            $failPublic
        ) {
            $notSet = [];
            if (empty($recipientSafety)) {
                $notSet[] = 'safety-recipient';
            }
            if (empty($senderAddress)) {
                $notSet[] = 'sender-address';
            }
            if (empty($recipientAddress)) {
                $notSet[] = 'recipient-address';
            }
            if (empty($nameOfProductionEnv)) {
                $notSet[] = 'name-of-production-env';
            }
            if ($failPublic) {
                if ((empty($approvedRecipients) && empty($approvedRecipientDomains))) {
                    if (empty($approvedRecipients)) {
                        $notSet[] = 'approved-recipients';
                    }
                    if (empty($approvedRecipientDomains)) {
                        $notSet[] = 'approved-recipient-domains';
                    }
                }
            }
            throw new Exception(
                'Required Mailora config (mailora.defaults) values not set (' . implode(', ', $notSet) . ')'
            );
        }
    }

    /**
     * @param $input array
     * @return bool|Exception
     * @throws Exception
     */
    public function sendPublic($input)
    {
        $this->ensureConfigSet(true);
        $email = $this->getMailable($input);

        if ($email === false) {
            return false;
        }

        $this->setSender($input, $email);
        if (!$this->checkAndSetRecipient($input, $email)) {
            self::error('Unauthorized recipient attempted. ($input: ' . json_encode($input) . ' )');
            return false;
        };
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);
        $this->setAttachments($input, $email);

        Mail::send($email);
    }

    /**
     * @param $input
     * @return bool|Exception
     * @throws Exception
     */
    public function sendSecure($input)
    {
        $this->ensureConfigSet();
        $email = $this->getMailable($input);

        if ($email === false) {
            return false;
        }

        $this->setSender($input, $email);
        if (!$this->checkAndSetRecipient($input, $email, false)) {
            self::error('Unauthorized recipient attempted. ($input: ' . json_encode($input) . ' )');
            return false;
        };
        $this->setSubject($input, $email);
        $this->setReplyTo($input, $email);
        $this->setAttachments($input, $email);

        Mail::send($email);
    }

    // -----------------------------------------------------------

    /**
     * @param $input
     *
     * @return Mailable|bool $email
     */
    private function getMailable($input)
    {
        $input['message'] = $input['message'] ?? '';

        $type = $this->getEmailType($input);
        $view = $this->getView($type, $input);
        $emailClass = $this->getEmailClass($type);

        if (!$emailClass && !$view) {
            return false;
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

            if (!empty($input['sender-name'])) {
                $senderName = $input['sender-name'];
            }
        }

        $email->from($senderAddress, $senderName);
    }

    /**
     * @param $input
     * @param Mailable $email
     * @param bool $public
     * @return bool
     * @throws Exception
     */
    private function checkAndSetRecipient($input, Mailable &$email, $public = true)
    {
        // todo: change singular 'recipient' to plural 'recipients' everywhere in the package

        $approvedRecipients = config('mailora.approved-recipients');
        $approvedRecipientDomains = config('mailora.approved-recipient-domains');

        $recipients = [
            [
                'address' => config('mailora.defaults.recipient-address'),
                'name' => config('mailora.defaults.recipient-name')
            ]
        ];

        // if recipients provided by request, use them
        if (!empty($input['recipient-address']) || !empty($input['recipient'])) {
            $recipientDecoded = json_decode($input['recipient'], true);

            if (is_array($recipientDecoded)) {
                $extractedRecipients = [];

                foreach ($recipientDecoded as $key => $value) {
                    if (is_array($value)) {
                        $aRecipient = [];
                        foreach ($value as $subKey => $subValue) {
                            if ($subKey === 'name' || $subKey === 'address') {
                                $aRecipient[$subKey] = $subValue;
                            }
                        }
                        $extractedRecipients[] = $aRecipient;
                    }
                    if (is_string($value)) {
                        if (count($recipientDecoded) >= 2) {
                            $extractedRecipients[] = $value;
                        } else {
                            $extractedRecipients = $recipientDecoded;
                        }
                    }
                }

                $recipients = $extractedRecipients;
            } else {
                $recipientAddress = $input['recipient-address'] ?? $input['recipient'];
                $recipients = [$recipientAddress];
                if (!empty($input['recipient-name'])) {
                    $recipientName = $input['recipient-name'];
                    $recipients = [['address' => $recipientAddress, 'name' => $recipientName]];
                }
            }

            // if request supplies recipient AND mailable has a recipient hardcoded, discard latter to use former only
            if ($email->to) {
                unset($email->to);
                $email->to = [];
            }

            if ($public) {
                foreach ($recipients as $key => $recipient) {
                    $addressApproved = false;
                    if (gettype($recipient) === 'array') {
                        $address = $recipient['address'];
                    } else {
                        $address = $recipient;
                    }

                    // validate address against whitelisted *domains*
                    if (!empty($approvedRecipientDomains)) {
                        foreach ($approvedRecipientDomains as $approvedRecipientDomain) {
                            if (preg_match('^[A-Za-z0-9._%+-]+@' . $approvedRecipientDomain . '$^', $address)) {
                                $addressApproved = true;
                            }
                        }
                    }

                    // if no whitelisted *domains* OR address was failed validation against domains...
                    // ... try against whitelisted exact addresses
                    if (empty($approvedRecipientDomains) || !$addressApproved) {
                        foreach ($approvedRecipients as $approvedRecipient) {
                            if ($address === $approvedRecipient) {
                                $addressApproved = true;
                            }
                        }
                    }

                    if (!$addressApproved) {
                        unset($recipients[$key]);
                        error_log('Mailora public send request made with unauthorized recipient: "' . $address . '"');
                    }
                }
            }
        } else { // check for Mailable-supplied value
            /*
             * If the recipient already set on Mailable object (maybe because custom class with a hardcoded recipient),
             * remove the hardcoded recipient, and store it in $recipients var so we can control it as per others. For
             * example, if we're not on production and therefore want to send only to the "safety recipient".
             */
            if (!empty($email->to)) {
                foreach ($email->to as $recipientFromMailable) {
                    $recipientsFromMailable[] = $recipientFromMailable;
                }
                $recipients = $recipientsFromMailable ?? [];
                unset($email->to);
            }
        }

        // If not production environment, discard previous $recipients values and use "safety" recipients configured
        if (app()->environment() !== config('mailora.name-of-production-env')) {
            if (!env('DISABLE_MAILORA_LOCAL_MAIL_RECIPIENT_SAFETY_FEATURE')) {
                $recipients = [config('mailora.safety-recipient')];
            }
        }

        if (empty($recipients)) {
            throw new \Exception('No recipient(s) set.');
        }

        foreach ($recipients as $recipient) {
            if (is_array($recipient)) {
                $email->to($recipient['address'], $recipient['name']);
            } else {
                $email->to($recipient);
            }
        }

        return true;
    }

    private function setSubject($input, Mailable &$email)
    {
        $email->subject(
            $input['subject'] ?? config('mailora.defaults.subject', 'General Inquiry - Subject not specified')
        );
    }

    private function setReplyTo($input, Mailable &$email)
    {
        if (!empty($input['reply-to'])) {
            $email->replyTo($input['reply-to']);
        } else {
            if ($input['users-email-set-reply-to'] ?? config('mailora.defaults.users-email-set-reply-to', false)) {
                if (current_user()) {
                    $email->replyTo(current_user()->getEmail());
                }
            }
        }
    }

    private function setAttachments($input, Mailable &$email)
    {
        if (!empty($input['attachment'])) {
            $input['attachments'][] = $input['attachment'];
        }

        foreach ($input['attachments'] ?? [] as $attachment) {
            if (is_a(\Illuminate\Http\UploadedFile::class, $attachment)) {
                /** @var \Illuminate\Http\UploadedFile $attachment */
                $email->attach(
                    $attachment,
                    [
                        'as' => $attachment->getClientOriginalName(),
                        'mimeType' => $attachment->getClientMimeType()
                    ]
                );
            }
        }
    }

    private static function error($message)
    {
        error_log($message);

        if (config('mailora.admin')) {
            $adminEmailAddressToSendMessageTo = config('mailora.admin');
            // todo: send email with $message
        }
    }

    private static function ensureSlashes(
        &$string,
        $backslashes = false,
        $omitFirstSlash = false,
        $omitLastSlash = false
    ) {
        $slash = $backslashes ? '\\' : '/';

        if ($string) {
            if (!$omitFirstSlash) {
                $startsWithForwardSlash = substr($string, 0, 1) === $slash;
                if (!$startsWithForwardSlash) {
                    $string = $slash . $string;
                }
            }
            if (!$omitLastSlash) {
                $endsWithForwardSlash = substr($string, -1) === $slash;
                if (!$endsWithForwardSlash) {
                    $string = $string . $slash;
                }
            }
        }
    }

    private function getEmailType($input)
    {
        return $input['type'] ?? config('mailora.defaults.type', 'general');
    }

    public static function getView($type, $input)
    {
        $view = 'mailora::general';

        $viewsRootDir = config('mailora.views-root-directory') ?? '/resources/views/';
        $viewsEmailDir = config('mailora.views-email-directory') ?? 'emails';

        self::ensureSlashes($viewsRootDir);
        self::ensureSlashes($viewsEmailDir, false, true);

        $customPotentialViewPathTruncated = $viewsEmailDir . $type;
        $customPotentialViewPathFull = base_path() . $viewsRootDir . $customPotentialViewPathTruncated . '.blade.php';

        if (file_exists($customPotentialViewPathFull)) {
            $view = $customPotentialViewPathTruncated;
        } else {
            if ($type !== 'general') {
                $message = 'Custom type specified does have corresponding custom view. Email not sent. ';
                $message .= json_encode($input);
                self::error($message);
                return false;
            }
        }

        return $view;
    }

    /**
     * @param $type
     * @return string
     */
    private function getEmailClass($type): string
    {
        $typePascalCased = str_replace('-', '', ucwords($type, '-'));

        $prospectiveClassFromMailora = 'Railroad\\Mailora\\Mail\\' . $typePascalCased;

        if (class_exists($prospectiveClassFromMailora)) {
            $emailClass = $prospectiveClassFromMailora;
        }

        $prospectiveClassFromApplication = config('mailora.mailables-namespace') . $typePascalCased;

        if (class_exists($prospectiveClassFromApplication)) {
            $emailClass = $prospectiveClassFromApplication;
        }

        return $emailClass ?? '\Railroad\Mailora\Mail\General';
    }
}