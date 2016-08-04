<?php

/** doChecks
 * Available checks:
 * 
 * should-contain           Check if the reponse contains given string
 * should-not-contain       Check if the response does not contain given string
 * should-be-equal-to       Check if the response is equal to given string
 **/
function doChecks($content, $options) {
    if (isset($options['should-contain']) && $check = $options['should-contain']) {
        echo sprintf("Checking if response contains *%s*.\n", $check);
        return strpos($content, $check) != false ? 1 : 0;
    } elseif (isset($options['should-not-contain']) && $check = $options['should-not-contain']) {
        echo sprintf("Checking if response does not contain *%s*.\n", $check);
        return strpos($content, $check) == false ? 1 : 0;
    } elseif (isset($options['should-be-equal-to']) && $check = $options['should-be-equal-to']) {
        echo sprintf("Checking if response is equal to *%s*.\n", $check);
        return $content == $check ? 1 : 0;
    }

    return (0);
}
