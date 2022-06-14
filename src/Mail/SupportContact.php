<?php

namespace Railroad\Mailora\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Railroad\Mailora\Services\MailService;

class SupportContact extends Mailable
{
    use Queueable, SerializesModels;

    public $input;
    public $view;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($input, $view)
    {
        $this->input = $input;

        if(!empty(user())){
            $this->view = 'support-contact-from-student';
            $this->input['studentName'] = user()->display_name;
            $this->input['studentEmail'] = user()->email;
            $this->input['studentId'] = user()->id;
            $this->view = MailService::getView('support-contact-from-student', []);
        }else{
            $this->view = MailService::getView('support-contact-from-public', []);
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->view);
    }
}
