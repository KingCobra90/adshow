<?php

$app->get('/units', function () use($app, $unitDao, $permissionsService) {
    if(!$permissionsService->checkPermission('view units')) {
        $app->notFound();
    }

    $availableUnits = $unitDao->getAll();
    if($availableUnits === null) {
        $app->error();
    }

    LayoutView::set_layout('layout/unit.tpl.php');
    $app->render('units/units.tpl.php', array('availableUnits' => $availableUnits));
});

$app->get('/units/:name', function ($name) use($app, $unitDao, $validationService, $permissionsService) {
    if(!$validationService->checkName($name) || !$permissionsService->checkPermission('view units')) {
       $app->notFound();
    }

    $unit = $unitDao->get($name);
    if($unit === null) {
        $app->notFound();
    }

    $availableUnits = $unitDao->getAll();
    if($availableUnits === null) {
        $app->error();
    }

    LayoutView::set_layout('layout/unit.tpl.php');
    $app->render('units/view.tpl.php', array('availableUnits' => $availableUnits, 'unit' => $unit));
});

$app->get('/add/unit', function () use($app, $unitDao, $validationService, $permissionsService) {
    if(!$permissionsService->checkPermission('view units') || !$permissionsService->checkPermission('add unit')) {
        $app->notFound();
    }

    $availableUnits = $unitDao->getAll();
    if($availableUnits === null) {
        $app->error();
    }

    $unit = new Unit();
    $unit->setDefault();

    $token = $validationService->generateToken(ValidationService::UNIT_FORM_TOKEN_NAME);

    LayoutView::set_layout('layout/unit.tpl.php');
    $app->render('units/edit.tpl.php', array('availableUnits' => $availableUnits, 'action' => 'create',
        'unit' => $unit, 'token' => $token));
});

$app->post('/add/unit', function () use($app, $unitDao, $validationService, $permissionsService) {
    if(!$permissionsService->checkPermission('view units') || !$permissionsService->checkPermission('add unit')) {
        $app->notFound();
    }

    $unit = new Unit();
    $unit->name = isset($_POST['name']) ? $_POST['name'] : '';
    $unit->type = isset($_POST['type']) ? $_POST['type'] : '';
    $unit->title = isset($_POST['title']) ? $_POST['title'] : '';
    $unit->weight = isset($_POST['weight']) ? $_POST['weight'] : 0;
    $unit->link = isset($_POST['link']) ? $_POST['link'] : '';
    $unit->imageUrl = isset($_POST['imageUrl']) ? $_POST['imageUrl'] : '';
    $unit->html = isset($_POST['html']) ? $_POST['html'] : '';

    $token = isset($_POST['token']) ? $_POST['token'] : '';

    $errors = $validationService->validateUnit($unit, $token);

    if($errors->hasErrors()) {
        $availableUnits = $unitDao->getAll();
        if($availableUnits === null) {
            $app->error();
        }

        $token = $validationService->generateToken(ValidationService::UNIT_FORM_TOKEN_NAME);

        LayoutView::set_layout('layout/unit.tpl.php');
        $app->render('units/edit.tpl.php', array('availableUnits' => $availableUnits, 'action' => 'create',
            'unit' => $unit, 'token' => $token, 'errors' => $errors));
    } else {
        $unit->setDefaultNotUsed();
        if($unitDao->insert($unit)) {
            $app->redirect('/units/' . $unit->name);
        } else {
            $app->error();
        }
    }
});

$app->get('/units/:name/edit', function ($name) use($app, $unitDao, $validationService, $permissionsService) {
    if(!$validationService->checkName($name) || !$permissionsService->checkPermission('view units') ||
        !$permissionsService->checkPermission('edit unit')) {
        $app->notFound();
    }

    $unit = $unitDao->get($name);
    if($unit === null) {
        $app->notFound();
    }

    $availableUnits = $unitDao->getAll();
    if($availableUnits === null) {
        $app->error();
    }

    $token = $validationService->generateToken(ValidationService::UNIT_FORM_TOKEN_NAME);

    LayoutView::set_layout('layout/unit.tpl.php');
    $app->render('units/edit.tpl.php', array('availableUnits' => $availableUnits, 'action' => 'edit',
        'unit' => $unit, 'token' => $token));
});

$app->post('/units/:name/edit', function ($name) use($app, $unitDao, $validationService, $permissionsService) {
    if(!$validationService->checkName($name) || !$permissionsService->checkPermission('view units') ||
        !$permissionsService->checkPermission('edit unit')) {
        $app->notFound();
    }

    $unit = $unitDao->get($name);
    if($unit === null) {
        $app->notFound();
    }

    $unit->title = isset($_POST['title']) ? $_POST['title'] : '';
    $unit->weight = isset($_POST['weight']) ? $_POST['weight'] : 0;
    $unit->link = isset($_POST['link']) ? $_POST['link'] : '';
    $unit->imageUrl = isset($_POST['imageUrl']) ? $_POST['imageUrl'] : '';
    $unit->html = isset($_POST['html']) ? $_POST['html'] : '';

    $token = isset($_POST['token']) ? $_POST['token'] : '';

    $errors = $validationService->validateUnit($unit, $token);

    if($errors->hasErrors()) {
        $availableUnits = $unitDao->getAll();
        if($availableUnits === null) {
            $app->error();
        }

        $token = $validationService->generateToken(ValidationService::UNIT_FORM_TOKEN_NAME);

        LayoutView::set_layout('layout/unit.tpl.php');
        $app->render('units/edit.tpl.php', array('availableUnits' => $availableUnits, 'action' => 'edit',
            'unit' => $unit, 'token' => $token, 'errors' => $errors));
    } else {
        $unit->setDefaultNotUsed();
        if($unitDao->update($unit)) {
            $app->redirect('/units/' . $unit->name);
        } else {
            $app->error();
        }
    }
});

$app->get('/units/:name/delete', function ($name) use($app, $unitDao, $validationService, $permissionsService) {
    if(!$validationService->checkName($name) || !$permissionsService->checkPermission('view units') ||
       !$permissionsService->checkPermission('delete unit')) {
       $app->notFound();
    }

    $unit = $unitDao->get($name);
    if($unit === null) {
        $app->notFound();
    }

    $availableUnits = $unitDao->getAll();
    if($availableUnits === null) {
        $app->error();
    }

    $token = $validationService->generateToken(ValidationService::UNIT_FORM_TOKEN_NAME);

    LayoutView::set_layout('layout/unit.tpl.php');
    $app->render('units/delete.tpl.php', array('availableUnits' => $availableUnits, 'unit' => $unit, 'token' => $token));
});

$app->post('/units/:name/delete', function ($name) use($app, $unitDao, $bindingDao, $validationService, $permissionsService) {
    if(!$validationService->checkName($name) || !$permissionsService->checkPermission('view units') ||
       !$permissionsService->checkPermission('delete unit')) {
       $app->notFound();
    }

    $unit = $unitDao->get($name);
    if($unit === null) {
        $app->notFound();
    }

    $token = isset($_POST['token']) ? $_POST['token'] : '';

    if(!$validationService->checkToken(ValidationService::UNIT_FORM_TOKEN_NAME, $token)) {
        $availableUnits = $unitDao->getAll();
        if($availableUnits === null) {
            $app->error();
        }

        $token = $validationService->generateToken(ValidationService::UNIT_FORM_TOKEN_NAME);

        LayoutView::set_layout('layout/unit.tpl.php');
        $app->render('units/delete.tpl.php', array('availableUnits' => $availableUnits, 'unit' => $unit,
            'errors' => '', 'token' => $token));
    } else {
        if($unitDao->delete($unit) && $bindingDao->deleteByUnit($unit)) {
            $app->redirect('/units');
        } else {
            $app->error();
        }
    }
});
