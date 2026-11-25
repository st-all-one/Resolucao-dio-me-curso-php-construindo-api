<?php

declare(strict_types=1);

// Rotas "web" não são usadas: o painel é registrado via then() em bootstrap/app.php
// com o grupo `api` (sem sessão/cookies) e a API sob /api/v1.
