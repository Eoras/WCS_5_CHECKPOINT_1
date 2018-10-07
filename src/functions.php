<?php

/**
 * @param string $lastname
 * @param string $firstname
 * @return string
 */
function fullName(string $lastname, string $firstname): string
{
    // mb_convert_case is better than strtoupper because it takes the accented capital letters.
    return mb_convert_case($lastname, MB_CASE_UPPER, "UTF-8") . " " . ucwords(strtolower($firstname));
}
