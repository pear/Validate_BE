<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Specific validation methods for International Standard Product Numbers (ISPN)
 *
 * PHP versions 4
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Validate
 * @package    Validate_ISPN
 * @author     Piotr Klaban <makler@man.torun.pl>
 * @author     Damien Seguy <dams@nexen.net>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Validate_ISPN
 */

/**
* Requires base class Validate
*/
require_once 'Validate.php';

/**
 * Data validation class for International Standard Product Numbers (ISPN)
 *
 * This class provides methods to validate:
 *  - ISBN (International Standard Book Number)
 *  - ISSN (International Standard Serial Number)
 *  - ISMN (International Standard Music Number)
 *  - EAN/UCC-8 number
 *  - EAN/UCC-13 number
 *  - EAN/UCC-14 number
 *  - UCC-12 (U.P.C.) ID number
 *  - SSCC (Serial Shipping Container Code)
 *
 * @category   Validate
 * @package    Validate_ISPN
 * @author     Piotr Klaban <makler@man.torun.pl>
 * @author     Damien Seguy <dams@nexen.net>
 * @copyright  2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Validate_ISPN
 */
class Validate_ISPN
{
    /**
     * Validate a ISBN number
     * The ISBN is a unique machine-readable identification number, 
     * which marks any book unmistakably. 
     *
     * This function checks given number according
     *
     * @param  string  $isbn number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @author Damien Seguy <dams@nexen.net>
     */
    function isbn($isbn)
    {
        if (preg_match("/[^0-9 IXSBN-]/", $isbn)) {
            return false;
        }

        if (!ereg("^ISBN", $isbn)){
            return false;
        }

        $isbn = ereg_replace('-', '', $isbn);
        $isbn = ereg_replace(' ', '', $isbn);
        $isbn = eregi_replace('ISBN', '', $isbn);
        if (strlen($isbn) != 10) {
            return false;
        }
        if (preg_match("/[^0-9]{9}[^0-9X]/", $isbn)){
            return false;
        }

        $t = 0;
        for ($i = 0; $i < strlen($isbn) - 1; $i++){
            $t += $isbn[$i]*(10-$i);
        }
        $f = $isbn[9];
        if ($f == 'X') {
            $t += 10;
        } else {
            $t += $f;
        }
        if ($t % 11) {
            return false;
        }
        return true;
    }


    /**
     * Validate an ISSN (International Standard Serial Number)
     *
     * This function checks given ISSN number
     * ISSN identifies periodical publications:
     * http://www.issn.org
     *
     * @param  string  $issn number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function issn($issn)
    {
        static $weights_issn = array(8,7,6,5,4,3,2);

        $issn = strtoupper($issn);
        $issn = eregi_replace('ISSN', '', $issn);
        $issn = str_replace(array('-', '/', ' ', "\t", "\n"), '', $issn);
        $issn_num = eregi_replace('X', '0', $issn);

        // check if this is an 8-digit number
        if (!is_numeric($issn_num) || strlen($issn) != 8) {
            return false;
        }

        return Validate::_checkControlNumber($issn, $weights_issn, 11, 11);
    }

    /**
     * Validate a ISMN (International Standard Music Number)
     *
     * This function checks given ISMN number (ISO Standard 10957)
     * ISMN identifies all printed music publications from all over the world
     * whether available for sale, hire or gratis--whether a part, a score,
     * or an element in a multi-media kit:
     * http://www.ismn-international.org/
     *
     * @param  string  $ismn ISMN number
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function ismn($ismn)
    {
        static $weights_ismn = array(3,1,3,1,3,1,3,1,3);

        $ismn = eregi_replace('ISMN', '', $ismn);
        $ismn = eregi_replace('M', '3', $ismn); // change first M to 3
        $ismn = str_replace(array('-', '/', ' ', "\t", "\n"), '', $ismn);

        // check if this is a 10-digit number
        if (!is_numeric($ismn) || strlen($ismn) != 10) {
            return false;
        }

        return Validate::_checkControlNumber($ismn, $weights_ismn, 10, 10);
    }


    /**
     * Validate a EAN/UCC-8 number
     *
     * This function checks given EAN8 number
     * used to identify trade items and special applications.
     * http://www.ean-ucc.org/
     * http://www.uc-council.org/checkdig.htm
     *
     * @param  string  $ean number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @see Validate_ISPN::process()
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function ean8($ean)
    {
        static $weights_ean8 = array(3,1,3,1,3,1,3);
        return Validate_ISPN::process($ean, 8, $weights_ean8, 10, 10);
    }

    /**
     * Validate a EAN/UCC-13 number
     *
     * This function checks given EAN/UCC-13 number used to identify
     * trade items, locations, and special applications (e.g., coupons)
     * http://www.ean-ucc.org/
     * http://www.uc-council.org/checkdig.htm
     *
     * @param  string  $ean number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @see Validate_ISPN::process()
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function ean13($ean)
    {
        static $weights_ean13 = array(1,3,1,3,1,3,1,3,1,3,1,3);
        return Validate_ISPN::process($ean, 13, $weights_ean13, 10, 10);
    }

    /**
     * Validate a EAN/UCC-14 number
     *
     * This function checks given EAN/UCC-14 number
     * used to identify trade items.
     * http://www.ean-ucc.org/
     * http://www.uc-council.org/checkdig.htm
     *
     * @param  string  $ean number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @see Validate_ISPN::process()
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function ean14($ean)
    {
        static $weights_ean14 = array(3,1,3,1,3,1,3,1,3,1,3,1,3);
        return Validate_ISPN::process($ean, 14, $weights_ean14, 10, 10);
    }

    /**
     * Validate a UCC-12 (U.P.C.) ID number
     *
     * This function checks given UCC-12 number used to identify
     * trade items, locations, and special applications (e.g., * coupons)
     * http://www.ean-ucc.org/
     * http://www.uc-council.org/checkdig.htm
     *
     * @param  string  $ucc number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @see Validate_ISPN::process()
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function ucc12($ucc)
    {
        static $weights_ucc12 = array(3,1,3,1,3,1,3,1,3,1,3);
        return Validate_ISPN::process($ucc, 12, $weights_ucc12, 10, 10);
    }

    /**
     * Validate a SSCC (Serial Shipping Container Code)
     *
     * This function checks given SSCC number
     * used to identify logistic units.
     * http://www.ean-ucc.org/
     * http://www.uc-council.org/checkdig.htm
     *
     * @param  string  $sscc number (only numeric chars will be considered)
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @see Validate_ISPN::process()
     * @author Piotr Klaban <makler@man.torun.pl>
     */
    function sscc($sscc)
    {
        static $weights_sscc = array(3,1,3,1,3,1,3,1,3,1,3,1,3,1,3,1,3);
        return Validate_ISPN::process($sscc, 18, $weights_sscc, 10, 10);
    }

    /**
     * Does all the work for EAN8, EAN13, EAN14, UCC12 and SSCC
     * and can be used for as base for similar kind of calculations
     * 
     * @param int $data number (only numeric chars will be considered)
     * @param int $lenght required length of number string
     * @param int $modulo (optionsl) number
     * @param int $subtract (optional) numbier
     * @param array $weights holds the weight that will be used in calculations for the validation
     * @return bool    true if number is valid, otherwise false
     * @access public
     * @see Validate::_checkControlNumber()
     */     
    function process($data, $length, &$weights, $modulo = 10, $subtract = 0)
    {
        //$weights = array(3,1,3,1,3,1,3,1,3,1,3,1,3,1,3,1,3);
        //$weights = array_slice($weights, 0, $length);

        $data = str_replace(array('-', '/', ' ', "\t", "\n"), '', $data);

        // check if this is a 18-digit number
        if (!is_numeric($data) || strlen($data) != $length) {
            return false;
        }

        return Validate::_checkControlNumber($data, $weights, $modulo, $subtract);
    }
}
?>