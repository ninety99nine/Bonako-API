<?php

namespace App\Services\CodeGenerator;

class CodeGeneratorService
{
    /**
     *  Generate a random 6 digit number
     *
     *  @param array $excludedCodes The 6 digit codes to exclude
     *  @return int
     */
    public static function generateRandomSixDigitNumber($excludedCodes = [])
    {
        $codeExists = true;

        while($codeExists == true) {

            //  Generate a random 6 digit number
            $randomNumber = mt_rand(1, 999999);

            //  Pad with leading "0" characters
            $randomCode = str_pad($randomNumber, 6, 0, STR_PAD_RIGHT);

            //  Check if the code is currently in use
            $codeExists = collect($excludedCodes)->contains($randomCode);

        }

        //  Return the random 6 digit code
        return $randomCode;
    }
}
