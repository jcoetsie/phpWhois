<?php
/**
 * phpWhois Example
 * 
 * This class supposed to be instantiated for using the phpWhois library
 * 
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @license
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 * 
 * @link http://phpwhois.pw
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 */

header('Content-Type: text/html; charset=UTF-8');

if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require_once __DIR__.'/vendor/autoload.php';
}

use phpWhois\Whois;
use phpWhois\Utils;

$out = implode('', file('example.html'));

$out = str_replace('{self}', $_SERVER['PHP_SELF'], $out);

$resout = extract_block($out, 'results');

if (isSet($_GET['query'])) {
    $query = $_GET['query'];

    if (!empty($_GET['output']))
        $output = $_GET['output'];
    else
        $output = '';

    $whois = new Whois();

    // Set to true if you want to allow proxy requests
    $allowproxy = false;

    // get faster but less acurate results
    $whois->deep_whois = empty($_GET['fast']);

    // To use special whois servers (see README)
    //$whois->UseServer('uk','whois.nic.uk:1043?{hname} {ip} {query}');
    //$whois->UseServer('au','whois-check.ausregistry.net.au');
    // Comment the following line to disable support for non ICANN tld's
    $whois->non_icann = true;

    $result = $whois->Lookup($query);
    $resout = str_replace('{query}', $query, $resout);
    $winfo = '';

    switch ($output) {
        case 'object':
            if ($whois->Query['status'] < 0) {
                $winfo = implode($whois->Query['errstr'], "\n<br></br>");
            } else {
                $utils = new Utils;
                $winfo = $utils->showObject($result);
            }
            break;

        case 'nice':
            if (!empty($result['rawdata'])) {
                $utils = new Utils;
                $winfo = $utils->showHTML($result);
            } else {
                if (isset($whois->Query['errstr']))
                    $winfo = implode($whois->Query['errstr'], "\n<br></br>");
                else
                    $winfo = 'Unexpected error';
            }
            break;

        case 'proxy':
            if ($allowproxy)
                exit(serialize($result));

        default:
            if (!empty($result['rawdata'])) {
                $winfo .= '<pre>' . implode($result['rawdata'], "\n") . '</pre>';
            } else {
                $winfo = implode($whois->Query['errstr'], "\n<br></br>");
            }
    }

    $resout = str_replace('{result}', $winfo, $resout);
} else
    $resout = '';

$out = str_replace('{ver}', $whois->CODE_VERSION, $out);
exit(str_replace('{results}', $resout, $out));

//-------------------------------------------------------------------------

function extract_block(&$plantilla, $mark, $retmark = '') {
    $start = strpos($plantilla, '<!--' . $mark . '-->');
    $final = strpos($plantilla, '<!--/' . $mark . '-->');

    if ($start === false || $final === false)
        return;

    $ini = $start + 7 + strlen($mark);

    $ret = substr($plantilla, $ini, $final - $ini);

    $final+=8 + strlen($mark);

    if ($retmark === false)
        $plantilla = substr($plantilla, 0, $start) . substr($plantilla, $final);
    else {
        if ($retmark == '')
            $retmark = $mark;
        $plantilla = substr($plantilla, 0, $start) . '{' . $retmark . '}' . substr($plantilla, $final);
    }

    return $ret;
}
