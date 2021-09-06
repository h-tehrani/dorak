<?php

namespace App\Repository\Eloquent;

use App\Models\Tag;
use App\Repository\TagRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{

    /**
     * UserRepository constructor.
     *
     * @param Tag $model
     */
    public function __construct(Tag $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * @return integer
     */
    public function remain(): int
    {
        return Tag::query()->where('user_id', Auth()->id())->count('id');
    }

    /**
     * @return Collection
     */
    public function history(): Collection
    {
        return Tag::query()->where('user_id', Auth()->id())->get();
    }

    /**
     * @return array
     */
    public function index(): array
    {
        $tags = Tag::query()->pluck('name')->toArray();
        return array_unique($tags);
    }

    /**
     * @return array
     */
    public function top(): array
    {
        return Tag::query()
            ->select('name')
            ->groupBy('name')
            ->orderByRaw('COUNT(*) DESC')
            ->take(3)
            ->get()->toArray();
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        Tag::query()->where('user_id', auth()->id())->delete();
    }
}
