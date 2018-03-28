<?php


namespace SITE\Helpers;


class Defines
{

    const APP_RESPONSE_TYPE_JSON   = 0;
    const APP_RESPONSE_TYPE_CUSTOM = 1;


    const TOKEN_TYPE_REGISTRATION    = "REGISTER";
    const TOKEN_TYPE_SIGN_IN         = "LOGIN";
    const TOKEN_TYPE_FORGOT_PASSWORD = "FORGOT_PASSWORD";

    const USER_ROLE_ADMIN            = '0';
    const USER_ROLE_MANAGER          = '1';
    const USER_ROLE_WAITER           = '2';



    const NOTIFICATION_TYPE_NOT_VERIFIED = 2;

    public static $availableFunctions = [
        'createUser',
        "signOut",
        'signIn',
    ];


}