<?php
// $Id: index.php,v 1.94 2007/12/26 08:46:48 dries Exp $

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 */
 

//Controlar que solo acceda desde la web de la soc
//$remote_ip = gethostbyname('www.acopiadorescba.com');
//if($_SERVER['REMOTE_ADDR'] == $remote_ip ) {

define('ACOPIADORESUSERS_SECRET_KEY', 'kdHaO6A423Ad');

function decrypt($string) {
   $key = ACOPIADORESUSERS_SECRET_KEY;
   $result = '';
   $string = base64_decode($string);
   for($i=0; $i<strlen($string); $i++) {
      $char = substr($string, $i, 1);
      $keychar = substr($key, ($i % strlen($key))-1, 1);
      $char = chr(ord($char)-ord($keychar));
      $result.=$char;
   }
   return $result;
}	

function encrypt ($string) {
    $key = ACOPIADORESUSERS_SECRET_KEY;
    $result = ''; 
    for ($i=0; $i<strlen ($string); $i++) { 
        $char = substr ($string, $i, 1); 
        $keychar = substr ($key, ($i % strlen ($key))-1, 1); 
        $char = chr (ord ($char)+ord ($keychar)); 
        $result.=$char; 
    } 
    return base64_encode ($result); 
}


require_once './includes/bootstrap.inc';
		

drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

if ( empty($_REQUEST["p"]) || empty($_REQUEST["u"]) ){
	drupal_set_message('Error: No se pasaron parametros', 'error');
	drupal_goto('');
}
		
$userRequest = array(
	'name' => $_REQUEST['u'],
	'pass' => decrypt($_REQUEST['p'])
);


if (!user_authenticate($userRequest)) {
	drupal_set_message('Error: Usuario o contraseña incorrectos', 'error');
}

// defaults redirect to front page
drupal_goto('');

