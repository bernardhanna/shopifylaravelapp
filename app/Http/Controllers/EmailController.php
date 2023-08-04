<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-05-23 16:27:27
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-23 16:27:34
 */


namespace App\Http\Controllers;

class EmailController extends Controller
{
    public function troubleshooting()
    {
        return view('email.troubleshooting');
    }
}
