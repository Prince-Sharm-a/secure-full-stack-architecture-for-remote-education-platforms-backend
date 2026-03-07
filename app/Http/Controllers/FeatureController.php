<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeatureController extends Controller
{
    //
    function sendTextMail($to, $subject, $message) {

        try {
            $headers  = "From: Sports Ovation <no-reply@query.com>\r\n";
            $headers .= "Reply-To: no-reply@query.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            return mail($to, $subject, $message, $headers);

        } catch (\Exception $e) {
            return false;
        }
    }
}
