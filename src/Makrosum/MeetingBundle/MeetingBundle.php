<?php

namespace Makrosum\MeetingBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MeetingBundle extends Bundle
{
    const ROLE_USER = "ROLE_USER";
    const ROLE_ANONYMOUS = "IS_AUTHENTICATED_ANONYMOUSLY";

    const GRANTED_SUPER_PERSONNEL = "GRANTED_SUPER_PERSONNEL";

    const GRANTED_SUPER_MEETING = "GRANTED_SUPER_MEETING";
    const GRANTED_CREATE_MEETING = "GRANTED_CREATE_MEETING";
    const GRANTED_EDIT_MEETING = "GRANTED_EDIT_MEETING";
    const GRANTED_STATUS_MEETING = "GRANTED_STATUS_MEETING";
    const GRANTED_DELETE_MEETING = "GRANTED_DELETE_MEETING";
    const GRANTED_REQUEST_MEETING = "GRANTED_REQUEST_MEETING";
    const GRANTED_MATTER_MEETING = "GRANTED_MATTER_MEETING";
    const GRANTED_REPORT_MEETING = "GRANTED_REPORT_MEETING";

    public static function array_merger(Array $array1, Array $array2 = null, Array $_ = null){

        $return = $array1;

        if(!is_null($array2)){

            foreach(@$array2 as $key => $value) {

                if (@isset($array1[$key])) {

                    if (gettype($return[$key]) != gettype($value)) {

                        $return[$key] = $value;

                    } else if (gettype($return[$key]) == gettype($value)) {

                        if (is_array($return[$key]) && is_array($value)) {

                            $return[$key] = self::array_merger($return[$key], $value);

                        }else{

                            $return[$key] = $value;

                        }

                    }

                } else {

                    $return[$key] = $value;

                }

            }

        }

        return $return;

    }

}
