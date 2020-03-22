<?php
$router->group(['prefix' => 'maintain'], function ($router) {
    $router->get('/', 'ShopMaintainController@index')->name('admin_maintain.index');
    $router->post('edit', 'ShopMaintainController@edit')->name('admin.maintain.edit');
});
