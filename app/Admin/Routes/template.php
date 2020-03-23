<?php
$router->group(['prefix' => 'template'], function ($router) {
    $router->get('/', 'ShopTemplateController@index')->name('admin_template.index');
    $router->post('changeTemplate', 'ShopTemplateController@changeTemplate')->name('admin_template.changeTemplate');
    $router->post('remove', 'ShopTemplateController@remove')->name('admin_template.remove');

    $router->get('/online', 'ShopTemplateOnlineController@index')->name('admin_template_online.index');
    $router->post('/online/install', 'ShopTemplateOnlineController@install')
    ->name('admin_template_online.install');
});
