<?php

declare(strict_types=1);

function is_partial_request(): bool {
    return isset($_GET['partial']) && $_GET['partial'] === '1';
}
