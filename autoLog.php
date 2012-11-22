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
		

drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

if ( empty($_REQUEST["token"]) || empty($_REQUEST["u"]) ){
	drupal_set_message('Error: No se pasaron parametros', 'error');
	drupal_goto('');
}
		
$userRequest = $_REQUEST['u'];


//Nueva version DS

$result = db_query("SELECT pv.value FROM {users} u left join {profile_values} pv on u.uid = pv.uid left join {profile_fields} pf on pv.fid = pf.fid WHERE LOWER(u.name) = LOWER('". $userRequest ."') and u.status = 1 " );
$userTemp = db_fetch_object($result);
			  
  
$keyRequest = $userTemp->value;
$tiempo = substr(time(), 0, strlen(time())-2);
$tokenValidate = sha1( $tiempo . "_" . $userRequest . "_" .  $keyRequest );

//	echo $tokenValidate;
if( $tokenValidate != $_REQUEST["token"] && 1==2 ){ // puse 1==2 para testear inicialmente sin el token
	drupal_set_message('Error: Error en el token', 'error');
	drupal_goto('');
}else{
	    

		//Array ( [name] => jaja [pass] => jojo [op] => Log in [form_build_id] => form-d11fc8da89d8b67053d34d1a7e2d7501 [form_id] => user_login_block )

		 //Inicio login
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


		//	$user = drupal_unpack($user);		
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

			//user_module_invoke('load', $array, $user);
		  }  else {
			drupal_set_message('Error: el usuario no existe', 'error');
			drupal_goto('/');
		  }
		 //fin login
		 
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

// defaults to redirect front page
drupal_goto('');

