<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/EnvironmentLoader.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../helpers/JsonResponse.php';
require_once __DIR__ . '/../helpers/Request.php';
require_once __DIR__ . '/../helpers/Util.php';
require_once __DIR__ . '/../helpers/SessionAuth.php';
require_once __DIR__ . '/../helpers/Cors.php';
require_once __DIR__ . '/../helpers/Upload.php';
require_once __DIR__ . '/../helpers/Slug.php';
require_once __DIR__ . '/../helpers/Validator.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../db/Database.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../repositories/ItemRepository.php';
require_once __DIR__ . '/../services/ItemService.php';
require_once __DIR__ . '/../repositories/CategoryRepository.php';
require_once __DIR__ . '/../services/CategoryService.php';
require_once __DIR__ . '/../repositories/ParameterRepository.php';
require_once __DIR__ . '/../repositories/DocumentTypeRepository.php';
require_once __DIR__ . '/../repositories/DocumentRepository.php';

Cors::apply();
Cors::handlePreflight(Request::method());
SessionAuth::start();
