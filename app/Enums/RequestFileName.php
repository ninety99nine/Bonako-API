<?php

namespace App\Enums;

enum RequestFileName:string {
    case STORE_LOGO = 'logo';
    case STORE_ADVERT = 'advert';
    case PROFILE_PHOTO = 'profile_photo';
    case PRODUCT_PHOTO = 'product_photo';
    case STORE_COVER_PHOTO = 'cover_photo';
    case TRANSACTION_PROOF_OF_PAYMENT_PHOTO = 'transaction_proof_of_payment_photo';
}
