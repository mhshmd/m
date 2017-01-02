<?php

namespace App\Libraries\KuotaWA;

#Library Email
use App\Functions\Gmail\PHPMailer;

class Email{

	public $email;

	public function __construct($subject, $messages){

		$mail = new PHPMailer();

        $mail->IsSMTP();

        $mail->SMTPAuth = true;

        $mail->SMTPSecure = 'ssl';

        $mail->Host = 'smtp.gmail.com';

        $mail->Port = 465; 

        $mail->Username = "shamad2402@gmail.com"; 

        $mail->Password = "@j4nzky94@";      

        $mail->SetFrom("shamad2402@gmail.com", "Muh. Shamad");

        $mail->Subject = $subject;

        $message = $messages;

        $mail->Body = $message;
        
        $mail->AddAddress("13.7741@stis.ac.id");

        $this->email = $mail;

	}

	public function send(){

        return $this->email->Send();

	}
}