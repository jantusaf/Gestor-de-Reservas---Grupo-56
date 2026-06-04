<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'LoginController::index'); // arranca en login
$routes->get('/contenido/principal', 'Home::index');


// LOGIN
$routes->get('/login', 'LoginController::index');   // muestra la vista login.php
$routes->post('/login/iniciar_sesion', 'LoginController::iniciar_sesion');// procesa el formulario de login
$routes->get('/logout', 'LoginController::logout'); // cierra sesión

// REGISTRO
$routes->get('/registrarse', 'UsuarioController::muestra_vista_registrarse');   // muestra la vista registrarse.php
$routes->post('/registrarse/guardar', 'UsuarioController::guardar');            // procesa el formulario de registro

// USUARIO
$routes->get('/usuario/perfil',      'UsuarioController::perfil');       // ver perfil del usuario logueado
$routes->post('/usuario/actualizar', 'UsuarioController::actualizar');   // actualizar datos del usuario
$routes->post('/usuario/baja',       'UsuarioController::dar_de_baja');  // dar de baja la cuenta


// CLIENTES
$routes->match(['get','post'], '/cliente/alta',         'ClienteController::altaCliente');
$routes->get('/cliente/listar',                         'ClienteController::listarClientes');
$routes->get('/cliente/editar/(:num)',                  'ClienteController::editarCliente/$1');
$routes->post('/cliente/actualizar/(:num)',              'ClienteController::actualizarCliente/$1');
$routes->get('/cliente/deshabilitar/(:num)',             'ClienteController::deshabilitarCliente/$1');
$routes->get('/cliente/habilitar/(:num)',                'ClienteController::habilitarCliente/$1');

// RECINTO
$routes->get('/recinto',                                'RecintoController::listarRecintos');
$routes->get('/recinto/listar',                         'RecintoController::listarRecintos');
$routes->get('/recinto/alta',                           'RecintoController::altaRecinto');
$routes->post('/recinto/guardar',                       'RecintoController::guardarRecinto');
$routes->get('/recinto/deshabilitar/(:num)',             'RecintoController::deshabilitarRecinto/$1');
$routes->get('/recinto/habilitar/(:num)',               'RecintoController::habilitarRecinto/$1');
$routes->get('/recinto/editar/(:num)',                  'RecintoController::editarRecinto/$1');
$routes->post('/recinto/actualizar/(:num)',              'RecintoController::actualizarRecinto/$1');


// RESERVAS
$routes->get('/reserva/crear',          'ReservaController::altaReserva');
$routes->post('/reserva/guardar',       'ReservaController::guardarReserva');
$routes->post('/reserva/horas',         'ReservaController::horasDisponibles');
$routes->get('/reserva/listar',            'ReservaController::listarReservas');
$routes->get('/reserva/cancelar/(:num)',   'ReservaController::cancelarReserva/$1');
$routes->get('/reserva/editar/(:num)',     'ReservaController::editarReserva/$1');
$routes->post('/reserva/actualizar/(:num)', 'ReservaController::actualizarReserva/$1');

//PAGOS
$routes->get('/pago/listar', 'PagoController::listar'); // muestra el listado de pagos
$routes->get('/pago/alta/(:num)', 'PagoController::alta/$1');// muestra el formulario de alta de pago para una reserva específica
$routes->post('/pago/guardar', 'PagoController::guardar'); // procesa el formulario de alta de pago




/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
