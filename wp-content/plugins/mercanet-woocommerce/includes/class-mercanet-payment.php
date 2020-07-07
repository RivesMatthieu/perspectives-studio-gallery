<?php

class Mercanet_Payment
{
    public static function generate_direct_payment($seal, $data, $interface_version, $url) {
        echo "<form name=\"redirectForm\" method=\"POST\" action=\"" . $url . "\" >" .
        "<input type=\"hidden\" name=\"Data\" value=\"". $data . "\">" .
        "<input type=\"hidden\" name=\"InterfaceVersion\" value=\"". $interface_version . "\">" .
        "<input type=\"hidden\" name=\"Seal\" value=\"" . $seal . "\">" .
        "<input type=\"hidden\" name=\"Encode\" value=\"base64\">" .
        "<noscript><input type=\"submit\" name=\"Go\" value=\"Click to continue\"/></noscript> </form>" .
        "<script type=\"text/javascript\"> document.redirectForm.submit(); </script>";
    }


    public static function generate_iframe_payment( $seal, $data, $interface_version, $url ) {

        return "<iframe name=\"redirectForm\" style=\"min-width:390px; min-height:600px; width:100%;\"></iframe> " .
            "<form id=\"redirectForm\" target=\"redirectForm\" method=\"POST\" action=\"" . $url . "\" >" .
            "<input type=\"hidden\" name=\"Data\" value=\"". $data . "\">" .
            "<input type=\"hidden\" name=\"Encode\" value=\"base64\">" .
            "<input type=\"hidden\" name=\"InterfaceVersion\" value=\"". $interface_version . "\">" .
            "<input type=\"hidden\" name=\"Seal\" value=\"" . $seal . "\">" .
            "<noscript><input type=\"submit\" name=\"Go\" value=\"Click to continue\"/></noscript> </form>" .
            "<script type=\"text/javascript\"> document.getElementById('redirectForm').submit();
            </script>";
    }

}
