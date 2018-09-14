<?php

/**
 * Generates a random string.
 *
 * @param int $length The length of the string to generate.
 * @param bool $complex Whether to return complex string. Defaults to false
 * @return string The random string.
 */
function random_str($length=8, $complex=false) {
    $set = array_merge(range(0, 9), range('A', 'Z'), range('a', 'z'));
    $str = array();

    // Complex strings have always at least 3 characters, even if $length < 3
    if($complex == true)
    {
        // At least one number
        $str[] = $set[my_rand(0, 9)];

        // At least one big letter
        $str[] = $set[my_rand(10, 35)];

        // At least one small letter
        $str[] = $set[my_rand(36, 61)];

        $length -= 3;
    }

    for($i = 0; $i < $length; ++$i)
    {
        $str[] = $set[my_rand(0, 61)];
    }

    // Make sure they're in random order and convert them to a string
    shuffle($str);

    return implode($str);
}

/**
 * Returns a securely generated seed integer
 *
 * @return int An integer equivalent of a secure hexadecimal seed
 */
function secure_seed_rng()
{
    $bytes = PHP_INT_SIZE;

    do
    {

        $output = secure_binary_seed_rng($bytes);

        // convert binary data to a decimal number
        if ($bytes == 4)
        {
            $elements = unpack('i', $output);
            $output = abs($elements[1]);
        }
        else
        {
            $elements = unpack('N2', $output);
            $output = abs($elements[1] << 32 | $elements[2]);
        }

    } while($output > PHP_INT_MAX);

    return $output;
}

/**
 * Returns a securely generated seed
 *
 * @return string A secure binary seed
 */
function secure_binary_seed_rng($bytes)
{
    $output = null;

    if(version_compare(PHP_VERSION, '7.0', '>='))
    {
        try
        {
            $output = random_bytes($bytes);
        } catch (Exception $e) {
        }
    }

    if(strlen($output) < $bytes)
    {
        if(@is_readable('/dev/urandom') && ($handle = @fopen('/dev/urandom', 'rb')))
        {
            $output = @fread($handle, $bytes);
            @fclose($handle);
        }
    }
    else
    {
        return $output;
    }

    if(strlen($output) < $bytes)
    {
        if(function_exists('mcrypt_create_iv'))
        {
            if (DIRECTORY_SEPARATOR == '/')
            {
                $source = MCRYPT_DEV_URANDOM;
            }
            else
            {
                $source = MCRYPT_RAND;
            }

            $output = @mcrypt_create_iv($bytes, $source);
        }
    }
    else
    {
        return $output;
    }

    if(strlen($output) < $bytes)
    {
        if(function_exists('openssl_random_pseudo_bytes'))
        {
            // PHP <5.3.4 had a bug which makes that function unusable on Windows
            if ((DIRECTORY_SEPARATOR == '/') || version_compare(PHP_VERSION, '5.3.4', '>='))
            {
                $output = openssl_random_pseudo_bytes($bytes, $crypto_strong);
                if ($crypto_strong == false)
                {
                    $output = null;
                }
            }
        }
    }
    else
    {
        return $output;
    }

    if(strlen($output) < $bytes)
    {
        if(class_exists('COM'))
        {
            try
            {
                $CAPI_Util = new COM('CAPICOM.Utilities.1');
                if(is_callable(array($CAPI_Util, 'GetRandom')))
                {
                    $output = $CAPI_Util->GetRandom($bytes, 0);
                }
            } catch (Exception $e) {
            }
        }
    }
    else
    {
        return $output;
    }

    if(strlen($output) < $bytes)
    {
        // Close to what PHP basically uses internally to seed, but not quite.
        $unique_state = microtime().@getmypid();

        $rounds = ceil($bytes / 16);

        for($i = 0; $i < $rounds; $i++)
        {
            $unique_state = md5(microtime().$unique_state);
            $output .= md5($unique_state);
        }

        $output = substr($output, 0, ($bytes * 2));

        $output = pack('H*', $output);

        return $output;
    }
    else
    {
        return $output;
    }
}

/**
 * Generates a cryptographically secure random number.
 *
 * @param int $min Optional lowest value to be returned (default: 0)
 * @param int $max Optional highest value to be returned (default: PHP_INT_MAX)
 */
function my_rand($min=0, $max=PHP_INT_MAX) {
    // backward compatibility
    if($min === null || $max === null || $max < $min)
    {
        $min = 0;
        $max = PHP_INT_MAX;
    }

    if(version_compare(PHP_VERSION, '7.0', '>='))
    {
        try
        {
            $result = random_int($min, $max);
        } catch (Exception $e) {
        }

        if(isset($result))
        {
            return $result;
        }
    }

    $seed = secure_seed_rng();

    $distance = $max - $min;
    return $min + floor($distance * ($seed / PHP_INT_MAX) );
}