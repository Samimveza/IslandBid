<?php

SessionAuth::logout();
JsonResponse::success(['message' => 'Logged out successfully.']);
