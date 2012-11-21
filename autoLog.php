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
	
require_once './includes/bootstrap.inc';
		
if ( !isset($_REQUEST["q"]) || !isset($_REQUEST["destination"]) || !isset($_REQUEST["token"]) || !isset($_REQUEST["u"]) ){
// lo comento temporalmente para que no redirija dado que no quiero mandar todos estos parametros para testing
//	header("location:index.php");
}


drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
		
$userRequest = $_REQUEST['u'];

  $result = db_query("SELECT * FROM {users} u WHERE LOWER(name) = LOWER('". $userRequest ."') and status = 1 " );

  if ($user = db_fetch_object($result)) {      
    
    // Verificar que sea un usuario del centro marcado como de login externo
    $profile = content_profile_load('profile',  $user->uid);    
    if (!empty($profile)) {
	if ( !acopiadoresusers_is_centro_login_externo($profile->field_user_centroprincipal[0]['nid']) ){
		drupal_set_message('Error: el usuario no es de un centro habilitado para login externo', 'error');
		drupal_goto('');
	}
     } else {
		drupal_set_message('Error: No se pudo recuperar el perfil del usuario', 'error');
		drupal_goto('');
     }
    $user = drupal_unpack($user);

    $user->roles = array();

    if ($user->uid) {
      $user->roles[DRUPAL_AUTHENTICATED_RID] = 'authenticated user';
    }
    else {
      $user->roles[DRUPAL_ANONYMOUS_RID] = 'anonymous user';
    }
    $result = db_query('SELECT r.rid, r.name FROM {role} r INNER JOIN {users_roles} ur ON ur.rid = r.rid WHERE ur.uid = %d', $user->uid);
    while ($role = db_fetch_object($result)) {
      $user->roles[$role->rid] = $role->name;
    }

     user_module_invoke('load', $array, $user);
  } else {
	drupal_set_message('Error: el usuario no existe', 'error');
	drupal_goto('/');
  }
  
//$keyRequest = $user->key;
$keyRequest = 'ppp';
$tiempo = substr(time(), 0, strlen(time())-2);
$tokenValidate = sha1( $tiempo . "_" . $userRequest . "_" .  $keyRequest );

//	echo $tokenValidate;
//if( $tokenValidate != $_REQUEST["token"] ){
if( 1 == 2 ){
	echo "Error autenticando.";//devolver un error de autenticación.
	exit();
}else{

		//Array ( [name] => jaja [pass] => jojo [op] => Log in [form_build_id] => form-d11fc8da89d8b67053d34d1a7e2d7501 [form_id] => user_login_block )

		$_POST['name'] = $userRequest;
		$_POST['pass'] = "2";
		$_POST['op'] = "Log in";
		$_POST['token'] = $tokenValidate;
		$_POST['form_build_id'] = "form-d11fc8da89d8b67053d34d1a7e2d7501";
		$_POST['form_id'] = "user_login_block";

		//Array ( [q] => node [destination] => node ) 
		
		
		$return = menu_execute_active_handler();
		
		// Menu status constants are integers; page content is a string.
		if (is_int($return)) {
		  switch ($return) {
			case MENU_NOT_FOUND:
			  drupal_not_found();
			  break;
			case MENU_ACCESS_DENIED:
			  drupal_access_denied();
			  break;
			case MENU_SITE_OFFLINE:
			  drupal_site_offline();
			  break;
		  }
		}
		elseif (isset($return)) {
		  // Print any value (including an empty string) except NULL or undefined:
		  print theme('page', $return);
		}
		
		drupal_page_footer();
	
}
