<?php

namespace Optix\Draftable;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait Draftable
{
    public static function bootDraftable()
    {
        static::addGlobalScope('published', function (Builder $query) {
            $query->where('published_at', '<=', Carbon::now())
                  ->whereNotNull('published_at');
        });
    }

    public function scopeWithDrafts(Builder $query)
    {
        $query->withoutGlobalScope('published');
    }

    public function scopeOnlyDrafts(Builder $query)
    {
        $query->withDrafts()
              ->where('published_at', '>', Carbon::now())
              ->orWhereNull('published_at');
    }

    public function isPublished()
    {
        return ! is_null($this->published_at) && $this->published_at <= now();
    }

    public function publish()
    {
        $this->schedule(Carbon::now());
    }

    public function schedule(Carbon $time)
    {
        $this->setPublishedAt($time);
    }

    public function draft()
    {
        $this->setPublishedAt(null);
    }

    protected function setPublishedAt($publishedAt)
    {
        $this->published_at = $publishedAt;
        $this->save();
    }
}
