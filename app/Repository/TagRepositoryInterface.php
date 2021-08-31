<?php

namespace App\Repository;

use App\Models\Tag;
use Illuminate\Support\Collection;

interface TagRepositoryInterface
{
    public function all(): Collection;

    public function remain(): int;

    public function history(): Collection;

    public function index(): array;

    public function top(): array;
}
