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
$routes->setDefaultMethod('test');
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
$routes->get('/', 'Home::index');
$routes->get('home', 'Home::test');
$routes->group("api/user", function ($routes) {
    $routes->get('index', 'User::index');
    $routes->post('login', 'User::login');
    $routes->post('logout', 'User::logout');
    $routes->get('islogin/(:any)', 'User::islogin/$1');
    // $routes->post('test-post/(:any)/(:any)', 'Employee::testpost/$1/$2');
});

$routes->group("api/form", function ($routes) {
    $routes->get('getallform/(:any)', 'Form::getallformtype/$1');
    $routes->get('getalljenispeminjaman/(:any)/(:any)', 'Form::getalljenispeminjaman/$1/$2');
    $routes->get('getjenispeminjaman/(:any)/(:any)', 'Form::getjenispeminjaman/$1/$2');
    $routes->get('getallpermohonan/(:any)/(:any)', 'Form::getallpermohonan/$1/$2');
    $routes->get('getallpermohonan/(:any)', 'Form::getallpermohonan/$1');
    $routes->get('countnotif/(:any)', 'Form::countunreadnotif/$1');
    $routes->get('getpermohonan/(:any)/(:any)/(:any)', 'Form::getpermohonan/$1/$2/$3');
    $routes->post('updatestatus/(:any)', 'Form::updatestatus/$1');
    $routes->get('getpermohonanforedit/(:any)/(:any)', 'Form::getpermohonanforedit/$1/$2');
    $routes->post('deletepermohonan/(:any)', 'Form::deletepermohonan/$1');
    $routes->get('getpdffilename/(:any)/(:any)', 'Form::getpdffilename/$1/$2');
    $routes->post('createpermohonan/(:any)', 'Form::createpermohonan/$1');
    $routes->post('markasread/(:any)', 'Form::markasread/$1');
    $routes->get('getunreadpermohonan/(:any)', 'Form::getunreadpermohonan/$1');
    $routes->get('generatepdf/(:any)/(:any)', 'Form::generatepdf/$1/$2');
});


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
