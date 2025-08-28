<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');


//***************************
//    AUTHENTICATION
//***************************

//USER REGISTRATION
$routes->post('/api/v1/user/registration', 'Api\V1\Authentication\UserSignUp::handleUsersignup');
$routes->post('/api/v1/user/verify-email', 'Api\V1\Authentication\UserSignUp::handleUserVerify');
//USER VERIFICATION
$routes->post('/api/v1/user/login', 'Api\V1\Authentication\UserLogin::handleUserLogin');
//USER PROFILE
$routes->get('/api/v1/user/profile', 'Api\V1\Authentication\UserProfile::handleUserProfile', ['filter' => 'jwt']);