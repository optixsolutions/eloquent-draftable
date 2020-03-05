<?php

namespace Optix\Draftable;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder withDrafts
 * @method static Builder onlyDrafts
 */
trait Draftable
{
    public static function bootDraftable()
    {
        self::addGlobalScope('published', function (Builder $query) {
            $query
                ->whereNotNull('published_at')
                ->where('published_at', '<=', Carbon::now());
        });
    }

    public function scopeWithDrafts(Builder $query)
    {
        $query->withoutGlobalScope('published');
    }

    public function scopeOnlyDrafts(Builder $query)
    {
        $query->withDrafts()->where(function (Builder $query) {
            $query
                ->whereNull('published_at')
                ->orWhere('published_at', '>', Carbon::now());
        });
    }

    public function isPublished()
    {
        return ! is_null($this->published_at)
            && $this->published_at <= Carbon::now();
    }

    public function isDraft()
    {
        return ! $this->isPublished();
    }

    public function setPublishedAt($date)
    {
        if (! is_null($date)) {
            $date = Carbon::parse($date);
        }

        $this->published_at = $date;

        return $this;
    }

    public function setPublished(bool $published)
    {
        if (! $published) {
            return $this->setPublishedAt(null);
        }

        if ($this->isDraft()) {
            return $this->setPublishedAt(Carbon::now());
        }

        return $this;
    }

    public function publishAt($date)
    {
        $this->setPublishedAt($date)->save();

        return $this;
    }

    public function publish(bool $publish = true)
    {
        $this->setPublished($publish)->save();

        return $this;
    }

    public function draft()
    {
        return $this->publish(false);
    }
}
